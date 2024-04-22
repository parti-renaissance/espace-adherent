<?php

namespace App\Security;

use App\BesoinDEurope\Inscription\FinishInscriptionRedirectHandler;
use App\Entity\Adherent;
use App\Entity\FailedLoginAttempt;
use App\Membership\MembershipSourceEnum;
use App\OAuth\App\AuthAppUrlManager;
use App\Repository\FailedLoginAttemptRepository;
use App\Security\Http\Session\AnonymousFollowerSession;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;
use Symfony\Component\Security\Core\Authentication\Token\NullToken;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\Exception\InvalidCsrfTokenException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Guard\Authenticator\AbstractFormLoginAuthenticator;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

class LoginFormGuardAuthenticator extends AbstractFormLoginAuthenticator
{
    use TargetPathTrait;

    private $urlGenerator;
    private $csrfTokenManager;
    private $passwordEncoder;
    private $anonymousFollowerSession;
    private $failedLoginAttemptRepository;
    private $apiPathPrefix;
    private AuthAppUrlManager $appUrlManager;
    private FinishInscriptionRedirectHandler $besoinDEuropeRedirectHandler;
    private ?string $currentAppCode = null;

    public function __construct(
        UrlGeneratorInterface $urlGenerator,
        CsrfTokenManagerInterface $csrfTokenManager,
        UserPasswordEncoderInterface $passwordEncoder,
        AnonymousFollowerSession $anonymousFollowerSession,
        FailedLoginAttemptRepository $failedLoginAttemptRepository,
        AuthAppUrlManager $appUrlManager,
        FinishInscriptionRedirectHandler $besoinDEuropeRedirectHandler,
        string $apiPathPrefix
    ) {
        $this->urlGenerator = $urlGenerator;
        $this->csrfTokenManager = $csrfTokenManager;
        $this->passwordEncoder = $passwordEncoder;
        $this->anonymousFollowerSession = $anonymousFollowerSession;
        $this->failedLoginAttemptRepository = $failedLoginAttemptRepository;
        $this->apiPathPrefix = $apiPathPrefix;
        $this->appUrlManager = $appUrlManager;
        $this->besoinDEuropeRedirectHandler = $besoinDEuropeRedirectHandler;
    }

    public function supports(Request $request)
    {
        return 'app_user_login_check' === $request->attributes->get('_route')
            && $request->isMethod('POST');
    }

    public function getCredentials(Request $request)
    {
        $credentials = [
            'emailAddress' => $request->request->get('_login_email'),
            'password' => $request->request->get('_login_password'),
            'csrf_token' => $request->request->get('_login_csrf'),
        ];

        $request->getSession()->set(Security::LAST_USERNAME, $credentials['emailAddress']);

        $this->currentAppCode = $this->appUrlManager->getAppCodeFromRequest($request);

        return $credentials;
    }

    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        $token = new CsrfToken('authenticate', $credentials['csrf_token']);
        if (!$this->csrfTokenManager->isTokenValid($token)) {
            throw new InvalidCsrfTokenException();
        }

        $user = $userProvider->loadUserByUsername($credentials['emailAddress']);

        if (!$user) {
            throw new CustomUserMessageAuthenticationException('Invalid credentials.');
        }

        return $user;
    }

    /** @param UserInterface|Adherent $user */
    public function checkCredentials($credentials, UserInterface $user)
    {
        if (!$this->passwordEncoder->isPasswordValid($user, $credentials['password'])) {
            return false;
        }

        if (
            null !== $user->getSource()
            && MembershipSourceEnum::RENAISSANCE !== $user->getSource()
            && $this->currentAppCode !== $user->getSource()
        ) {
            return false;
        }

        return true;
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        if (!$token instanceof AnonymousToken && !$token instanceof NullToken && $this->anonymousFollowerSession->isStarted()) {
            return $this->anonymousFollowerSession->terminate();
        }

        $targetPath = $this->getTargetPath($request->getSession(), $providerKey);

        if ($redirect = $this->besoinDEuropeRedirectHandler->redirectToCompleteInscription($targetPath)) {
            return $redirect;
        }

        if ($targetPath) {
            return new RedirectResponse($targetPath);
        }

        /** @var Adherent $adherent */
        $user = $token->getUser();

        if ($user && $this->currentAppCode) {
            return new RedirectResponse($this->appUrlManager->getUrlGenerator($this->currentAppCode)->generateForLoginSuccess($user));
        }

        return new RedirectResponse($this->urlGenerator->generate('app_search_events'));
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        $this->failedLoginAttemptRepository->save(FailedLoginAttempt::createFromRequest($request));

        $this->currentAppCode = $this->appUrlManager->getAppCodeFromRequest($request);

        return parent::onAuthenticationFailure($request, $exception);
    }

    public function start(Request $request, ?AuthenticationException $authException = null)
    {
        if (
            \in_array('application/json', $request->getAcceptableContentTypes())
            || 0 === mb_strpos($request->getPathInfo(), $this->apiPathPrefix)
        ) {
            return new JsonResponse('Unauthorized', 401);
        }

        $this->currentAppCode = $this->appUrlManager->getAppCodeFromRequest($request);

        return parent::start($request, $authException);
    }

    protected function getLoginUrl()
    {
        if ($this->currentAppCode) {
            return $this->appUrlManager->getUrlGenerator($this->currentAppCode)->generateLoginLink();
        }

        return $this->urlGenerator->generate('app_user_login');
    }
}
