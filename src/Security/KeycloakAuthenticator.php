<?php

namespace App\Security;

use App\Entity\Settings;
use App\Entity\Team;
use App\Entity\User;
use App\Repository\TeamRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use KnpU\OAuth2ClientBundle\Security\Authenticator\OAuth2Authenticator;
use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Csrf\TokenStorage\TokenStorageInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;
use Symfony\Component\Security\Http\Util\TargetPathTrait;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class KeycloakAuthenticator extends OAuth2Authenticator implements AuthenticationEntryPointInterface
{
    use TargetPathTrait;

    public function __construct(
        private readonly ?string                $groupApiUserId,
        private readonly ?array                 $groupApiRoles,
        private readonly bool                   $groupApiGrantAdmin,
        private readonly LoggerInterface        $logger,
        private readonly ParameterBagInterface  $parameterBag,
        private readonly TokenStorageInterface  $tokenStorage,
        private readonly ClientRegistry         $clientRegistry,
        private readonly EntityManagerInterface $em,
        private readonly RouterInterface        $router,
        private readonly TeamRepository         $teamRepository,
        private readonly HttpClientInterface    $groupClient,
    )
    {
    }

    public function supports(Request $request): bool
    {
        // continue ONLY if the current ROUTE matches the check ROUTE
        return $request->attributes->get('_route') === 'connect_keycloak_check';
    }

    public function authenticate(Request $request): Passport
    {
        $client = $this->clientRegistry->getClient('keycloak_main');
        $accessToken = $this->fetchAccessToken($client);
        $request->getSession()->set('id_token', $accessToken->getValues()['id_token']);
        $passport = new SelfValidatingPassport(
            new UserBadge($accessToken->getToken(), function () use ($accessToken, $client,) {
                $keycloakUser = $client->fetchUserFromToken(accessToken: $accessToken);
                $user = $this->getUserForKeycloakUser(keycloakUser: $keycloakUser);
                $this->persistUser(user: $user, keycloakUser: $keycloakUser);
                return $user;
            }
            )
        );
        $passport->setAttribute('id_token', 'null');
        $passport->setAttribute('scope', 'openid');

        return $passport;
    }

    public function onAuthenticationSuccess(
        Request $request,
        TokenInterface $token,
        string $firewallName,
    ): ?Response
    {
        $targetUrl = $this->getTargetPath($request->getSession(), 'main');
        if (!$targetUrl) {
            $targetUrl = $this->router->generate('dashboard');
        }
        return new RedirectResponse($targetUrl);
    }

    public function onAuthenticationFailure(
        Request $request,
        AuthenticationException $exception,
    ): ?Response
    {
        $this->logger->error($exception->getMessage());
        return new RedirectResponse($this->router->generate('no_team'));
    }

    /*
     * Called when authentication is needed, but it's not sent.
     * This redirects to the 'login'.
     */
    public function start(
        Request $request,
        AuthenticationException $authException = null,
    ): RedirectResponse
    {
        $targetUrl = $this->router->generate('login_keycloak');
        return new RedirectResponse($targetUrl);
    }

    private function getTeamsFromKeycloakGroups(ResourceOwnerInterface $keycloakUser): ArrayCollection
    {
        $userTeams = new ArrayCollection();

        if (isset($keycloakUser->toArray()['groups'])) {
            $groups = $keycloakUser->toArray()['groups'];
            $teams = $this->teamRepository->findAll();

            foreach ($groups as $keycloakGroup) {
                foreach ($teams as $team) {
                    if ($team->getKeycloakGroup() === $keycloakGroup) {
                        $userTeams->add($team);
                    }
                }
            }
        }
        return $userTeams;
    }

    private function getEmailForKeycloakUser(ResourceOwnerInterface $keycloakUser): string {
        try {
            // FIXME: ResourceOwnerInterface cannot have method getEmail()
            return $keycloakUser->getEmail();
        } catch (Exception $e) {
            try {
                return $keycloakUser->toArray()['preferred_username'];
            } catch (Exception $e) {
            }
        }

        return '';
    }

    private function getRolesForKeycloakUser(ResourceOwnerInterface $keycloakUser): array
    {
        $clientId = $this->parameterBag->get('KEYCLOAK_ID'); // name of keycloak client
        $this->parameterBag->get('superAdminRole'); // name of super admin role in keycloak

        $roles = [];

        $clientData = $keycloakUser->toArray()['resource_access'][$clientId] ?? null;

        if ($clientData && isset($clientData['roles'])) {
            $superAdminRole = $this->parameterBag->get('superAdminRole');
            if (in_array($superAdminRole, $clientData['roles'])) {
                $roles [] = 'ROLE_ADMIN';
            }
        }

        return $roles;
    }

    private function getUserForKeycloakUser(ResourceOwnerInterface $keycloakUser): User
    {
        $email = $this->getEmailForKeycloakUser(keycloakUser: $keycloakUser);
        $id = $keycloakUser->getId();

        // 1) the user has logged in with keycloak before
        $user = $this->em->getRepository(User::class)->findOneBy(array('keycloakId' => $id));

        // 2) it is an old user who has never logged in from keycloak
        if (!$user) {
            $user = $this->em->getRepository(User::class)->findOneBy(array('email' => $email));
        }

        // 3) the user has never logged in with this email address or keycloak
        if (!$user) {
            $user = new User();
            $user->setCreatedAt(createdAt: new \DateTime());
        }

        return $user;
    }

    private function persistUser(User $user, ResourceOwnerInterface $keycloakUser): User
    {
        $email = $this->getEmailForKeycloakUser(keycloakUser: $keycloakUser);
        $user->setLastLogin(new \DateTime());
        $user->setEmail(email: $email);
        $user->setUsername(username: $email);
        $user->setKeycloakId(keycloakId: $keycloakUser->getId());
        $user->setFirstName(firstName: $keycloakUser->toArray()['given_name'] ?? '');
        $user->setLastName(lastName: $keycloakUser->toArray()['family_name'] ?? '');
        $user->setRoles(roles: $this->getRolesForKeycloakUser(keycloakUser: $keycloakUser));

        $settings = $this->em->getRepository(Settings::class)->findOne();
        if ($settings) {
            switch ($settings->getGroupMapping()) {
                case Settings::KEYCLOAK_GROUP_MAPPING:
                    $user->setTeams(teams: $this->getTeamsFromKeycloakGroups(keycloakUser: $keycloakUser));
                    break;
                case Settings::API_GROUP_MAPPING:
                    $teams = $this->syncApiGroups($keycloakUser);
                    foreach ($teams as $team) {
                        $user->addTeam($team);
                        if ($this->groupApiGrantAdmin) {
                            $team->addAdmin($user);
                            $this->em->persist($team);
                        }
                    }
                    break;
            }
        }

        $this->em->persist($user);
        $this->em->flush();
        return $user;
    }

    /**
     * @param ResourceOwnerInterface $keycloakUser
     * @return Collection<Team>
     */
    private function syncApiGroups(ResourceOwnerInterface $keycloakUser): Collection {
        try {
            $userId = $keycloakUser->toArray()[$this->groupApiUserId];
            $response = $this->groupClient->request('GET', "/v1/users/$userId/rbac-structure");
            $responsePayload = $response->toArray();

            $groups = array_combine(
                array_column($responsePayload['groups'], 'divisionKey'),
                $responsePayload['groups']
            );

            $roleDivisions = $this->getGroupsOfMatchingRoles($responsePayload['roles']);
            $roleGroups = array_filter($groups, function ($groupKey) use ($roleDivisions) {
                return in_array($groupKey, $roleDivisions);
            }, ARRAY_FILTER_USE_KEY);

            return $this->createTeams($roleGroups, $groups);
        } catch (\Throwable $e) {
            $this->logger->error("Exception \"{$e->getMessage()}\" at {$e->getFile()} line {$e->getLine()}");
            return new ArrayCollection();
        }
    }

    private function getGroupsOfMatchingRoles(array $roles) {
        $teamAdminRoles = array_filter($roles, function ($role) {
            return in_array($role['id'], $this->groupApiRoles);
        });

        return array_map(function ($role) {
            return $role['divisionKey'];
        }, $teamAdminRoles);
    }

    /**
     * @throws Exception
     */
    private function createTeams(array $userGroups, array $groupsTree): Collection {
        $teams = [];
        foreach ($userGroups as $group) {
            $teams[] = $this->createTeamHierarchy($group, $groupsTree);
        }
        return new ArrayCollection($teams);
    }

    /**
     * @throws Exception
     */
    private function createTeamHierarchy(array $group, array $groupsTree): ?Team {
        if (!array_key_exists('parentKey', $group)) {
            throw new Exception('Invalid group: '.implode(',', $group));
        }

        if (!$group['parentKey']) {
            return $this->getTeam($group);
        }

        if (!array_key_exists($group['parentKey'], $groupsTree)) {
            throw new Exception('Missing group in tree: '.implode(',', $group));
        }

        $parent = $this->createTeamHierarchy($groupsTree[$group['parentKey']], $groupsTree);

        return $this->getTeam($group, $parent);
    }

    private function getTeam(array $group, Team $parent = null): Team {
        $team = $this->teamRepository->findOneBy([
            'immutable' => 1,
            'name' => $group['displayName'],
        ]);

        if (!$team) {
            $team = new Team();
            $team->setName($group['displayName'])
                ->setImmutable(true)
                ->setParent($parent)
                ->setActiv(true)
                ->setStrasse('')
                ->setPlz('')
                ->setStadt('')
                ->setCeo('');
            $this->em->persist($team);
            $this->em->flush();
        }

        return $team;
    }
}
