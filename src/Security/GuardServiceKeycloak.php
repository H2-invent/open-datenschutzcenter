<?php


namespace App\Security;

use App\Entity\Settings;
use App\Entity\Team;
use App\Entity\User;
use App\Repository\TeamRepository;
use Doctrine\ORM\EntityManagerInterface;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use KnpU\OAuth2ClientBundle\Client\Provider\KeycloakClient;
use KnpU\OAuth2ClientBundle\Security\Authenticator\SocialAuthenticator;
use Stevenmaguire\OAuth2\Client\Provider\KeycloakResourceOwner;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Csrf\TokenStorage\TokenStorageInterface;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

class GuardServiceKeycloak extends SocialAuthenticator
{
    use TargetPathTrait;
    private $clientRegistry;
    private $em;
    private $router;
    private $tokenStorage;
    private $userManager;
    private $teamRepository;

    public function __construct(TokenStorageInterface $tokenStorage, ClientRegistry $clientRegistry, EntityManagerInterface $em, RouterInterface $router, TeamRepository $teamRepository)
    {
        $this->clientRegistry = $clientRegistry;
        $this->em = $em;
        $this->router = $router;
        $this->tokenStorage = $tokenStorage;
        $this->teamRepository = $teamRepository;
    }

    public function supports(Request $request)
    {
        // continue ONLY if the current ROUTE matches the check ROUTE
        return $request->attributes->get('_route') === 'connect_keycloak_check';
    }

    public function getCredentials(Request $request)
    {

        return $this->fetchAccessToken($this->getauth0Client());
    }

    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        /** @var KeycloakResourceOwner $keycloakUser */
        $keycloakUser = $this->getauth0Client()->fetchUserFromToken($credentials);
        $email = $keycloakUser->getEmail();
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
            $user->setPassword('123');
            $user->setUuid($email);
            $user->setCreatedAt(new \DateTime());
        }

        $this->persistUser($user, $keycloakUser);
        return $user;
    }

    /**
     * @param $user
     * @param $keycloakUser
     */
    private function getTeamsFromKeycloakGroups($user, $keycloakUser)
    {
        $settings = $this->em->getRepository(Settings::class)->findOne();

        if (isset($keycloakUser->toArray()['groups']) && $settings && $settings->getUseKeycloakGroups()) {
            $teams = [];
            $groups = $keycloakUser->toArray()['groups'];

            foreach ($groups as $group) {
                $team = $this->teamRepository->findOneBy(array('keycloakGroup' => $group));
                if ($team) {
                    $teams[] = $team;
                }
            }
            $user->setTeams($teams);
        }
    }

    private function persistUser($user, $keycloakUser) {
        $email = $keycloakUser->getEmail();
        $id = $keycloakUser->getId();

        $user->setLastLogin(new \DateTime());
        $user->setEmail($email);
        $user->setUsername($email);
        $user->setKeycloakId($id);
        $this->getTeamsFromKeycloakGroups($user, $keycloakUser);
        if (isset($keycloakUser->toArray()['given_name'])) {
            $user->setFirstName($keycloakUser->toArray()['given_name']);
        }
        if (isset($keycloakUser->toArray()['family_name'])) {
            $user->setLastName($keycloakUser->toArray()['family_name']);
        }
        $this->em->persist($user);
        $this->em->flush();
        return $user;
    }

    /**
     * @return KeycloakClient
     */
    private function getauth0Client()
    {
        return $this->clientRegistry
            ->getClient('keycloak_main');
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {

        // change "app_homepage" to some route in your app
        $targetUrl = $this->getTargetPath($request->getSession(), 'main');
        if (!$targetUrl) {
            $targetUrl = $this->router->generate('dashboard');
        }

        return new RedirectResponse($targetUrl);

    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        return new RedirectResponse($this->router->generate('no_team'));
    }

    /**
     * Called when authentication is needed, but it's not sent.
     * This redirects to the 'login'.
     */
    public function start(Request $request, AuthenticationException $authException = null)
    {
        $targetUrl = $this->router->generate('login_keycloak');
        return new RedirectResponse($targetUrl);
    }

}



