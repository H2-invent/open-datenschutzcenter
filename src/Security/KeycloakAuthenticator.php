<?php

namespace App\Security;

use App\Entity\Settings;
use App\Entity\User;
use App\Repository\TeamRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use KnpU\OAuth2ClientBundle\Security\Authenticator\OAuth2Authenticator;
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

class KeycloakAuthenticator extends OAuth2Authenticator implements AuthenticationEntryPointInterface
{
    use TargetPathTrait;

    public function __construct(
        private readonly LoggerInterface        $logger,
        private readonly ParameterBagInterface  $parameterBag,
        private readonly TokenStorageInterface  $tokenStorage,
        private readonly ClientRegistry         $clientRegistry,
        private readonly EntityManagerInterface $em,
        private readonly RouterInterface        $router,
        private readonly TeamRepository         $teamRepository,
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
                $keycloakUser = $client->fetchUserFromToken($accessToken);
                $user = $this->getUserForKeycloakUser($keycloakUser);
                $this->persistUser($user, $keycloakUser);
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

    private function getTeamsFromKeycloakGroups($keycloakUser): ArrayCollection
    {
        $userTeams = new ArrayCollection();
        $settings = $this->em->getRepository(Settings::class)->findOne();

        if (isset($keycloakUser->toArray()['groups']) && $settings && $settings->getUseKeycloakGroups()) {
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

    private function getEmailForKeycloakUser($keycloakUser) {
        $email = null;
        try {
            $email = $keycloakUser->getEmail();
        } catch (\Exception $e) {
            try {
                $email = $keycloakUser->toArray()['preferred_username'];
            } catch (\Exception $e) {
            }
        }
        return $email;
    }

    private function getRolesForKeycloakUser($keycloakUser): array
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

    private function getUserForKeycloakUser($keycloakUser) {
        $email = $this->getEmailForKeycloakUser($keycloakUser);
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

    private function persistUser(User $user, $keycloakUser): User
    {
        $email = $this->getEmailForKeycloakUser(keycloakUser: $keycloakUser);
        $user->setLastLogin(new \DateTime());
        $user->setEmail(email: $email);
        $user->setUsername(username: $email);
        $user->setKeycloakId(keycloakId: $keycloakUser->getId());
        $user->setFirstName(firstName: $keycloakUser->toArray()['given_name'] ?? '');
        $user->setLastName(lastName: $keycloakUser->toArray()['family_name'] ?? '');
        $user->setTeams(teams: $this->getTeamsFromKeycloakGroups(keycloakUser: $keycloakUser));
        $user->setRoles(roles: $this->getRolesForKeycloakUser(keycloakUser: $keycloakUser));
        $this->em->persist($user);
        $this->em->flush();
        return $user;
    }
}
