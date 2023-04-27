<?php


namespace App\Security;


use App\Entity\FosUser;
use App\Entity\MyUser;
use App\Entity\Settings;
use App\Entity\User;
use App\Repository\TeamRepository;
use App\Service\IndexUserService;
use App\Service\ThemeService;
use App\Service\UserCreatorService;
use Doctrine\ORM\EntityManagerInterface;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use KnpU\OAuth2ClientBundle\Client\Provider\KeycloakClient;
use KnpU\OAuth2ClientBundle\Security\Authenticator\OAuth2Authenticator;
use KnpU\OAuth2ClientBundle\Security\Authenticator\SocialAuthenticator;
use League\OAuth2\Client\Provider\GoogleUser;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Csrf\TokenStorage\TokenStorageInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

class KeycloakAuthenticator extends OAuth2Authenticator implements AuthenticationEntryPointInterface
{
    use TargetPathTrait;

    private $clientRegistry;
    private $em;
    private $router;
    private $tokenStorage;
    private $userManager;
    private $paramterBag;

    private $teamRepository;

    private $logger;

    public function __construct(
        LoggerInterface        $logger,
        ParameterBagInterface  $parameterBag,
        TokenStorageInterface  $tokenStorage,
        ClientRegistry         $clientRegistry,
        EntityManagerInterface $em,
        RouterInterface        $router,
        TeamRepository         $teamRepository)
    {
        $this->clientRegistry = $clientRegistry;
        $this->em = $em;
        $this->router = $router;
        $this->tokenStorage = $tokenStorage;
        $this->paramterBag = $parameterBag;
        $this->logger = $logger;
        $this->teamRepository = $teamRepository;
    }

    public function supports(Request $request): bool
    {
        // continue ONLY if the current ROUTE matches the check ROUTE
        return $request->attributes->get('_route') === 'connect_keycloak_check';
    }

    public function getCredentials(Request $request)
    {
        return $this->fetchAccessToken($this->getauth0Client());
    }

    public function authenticate(Request $request): Passport
    {
        $client = $this->clientRegistry->getClient('keycloak_main');
        $accessToken = $this->fetchAccessToken($client);
        $request->getSession()->set('id_token', $accessToken->getValues()['id_token']);
        $passport = new SelfValidatingPassport(
            new UserBadge($accessToken->getToken(), function () use ($accessToken, $client,) {
                /** @var KeycloakUser $keycloakUser */
                $keycloakUser = $client->fetchUserFromToken($accessToken);
                try {
                    //When the keycloak USer delivers a
                    $email = $keycloakUser->getEmail();
                } catch (\Exception $e) {
                    try {
                        $email = $keycloakUser->toArray()['preferred_username'];
                    } catch (\Exception $e) {

                    }

                }
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
            )
        );
        $passport->setAttribute('id_token', 'null');
        $passport->setAttribute('scope', 'openid');

        return $passport;
    }


    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey): ?Response
    {

        // change "app_homepage" to some route in your app
        $targetUrl = $this->getTargetPath($request->getSession(), 'main');
        if (!$targetUrl) {
            $targetUrl = $this->router->generate('dashboard');
        }

        return new RedirectResponse($targetUrl);

    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
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

    /**
     * @param $user
     * @param $keycloakUser
     */
    private function getTeamsFromKeycloakGroups($user, $keycloakUser)
    {
        $settings = $this->em->getRepository(Settings::class)->findOne();

        if (isset($keycloakUser->toArray()['groups']) && $settings && $settings->getUseKeycloakGroups()) {
            $userTeams = [];
            $groups = $keycloakUser->toArray()['groups'];
            $teams = $this->teamRepository->findAll();

            foreach ($groups as $keycloakGroup) {
                foreach ($teams as $team) {
                    if ($team->getKeycloakGroup() === $keycloakGroup) {
                        $userTeams[] = $team;
                    }
                }
            }
            $user->setTeams($userTeams);
        }
    }

    private function persistUser($user, $keycloakUser)
    {
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


}



