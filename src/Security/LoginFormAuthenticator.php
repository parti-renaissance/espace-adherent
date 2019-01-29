<?php

namespace AppBundle\Security;

use AppBundle\Entity\FailedLoginAttempt;
use AppBundle\Repository\AdherentRepository;
use AppBundle\Repository\FailedLoginAttemptRepository;
use AppBundle\Security\Http\Session\AnonymousFollowerSession;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Exception\InvalidCsrfTokenException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Guard\Authenticator\AbstractFormLoginAuthenticator;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

class LoginFormAuthenticator extends AbstractFormLoginAuthenticator
{
    use TargetPathTrait;

    private $entityManager;
    private $urlGenerator;
    private $csrfTokenManager;
    private $passwordEncoder;
    private $adherentRepository;
    private $anonymousFollowerSession;
    private $failedLoginAttemptRepository;

    public function __construct(
        UrlGeneratorInterface $urlGenerator,
        CsrfTokenManagerInterface $csrfTokenManager,
        UserPasswordEncoderInterface $passwordEncoder,
        AdherentRepository $adherentRepository,
        AnonymousFollowerSession $anonymousFollowerSession,
        FailedLoginAttemptRepository $failedLoginAttemptRepository
    ) {
        $this->urlGenerator = $urlGenerator;
        $this->csrfTokenManager = $csrfTokenManager;
        $this->passwordEncoder = $passwordEncoder;
        $this->adherentRepository = $adherentRepository;
        $this->anonymousFollowerSession = $anonymousFollowerSession;
        $this->failedLoginAttemptRepository = $failedLoginAttemptRepository;
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

        $request->getSession()->set(
            Security::LAST_USERNAME,
            $credentials['emailAddress']
        );

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

    public function checkCredentials($credentials, UserInterface $user)
    {
        return $this->passwordEncoder->isPasswordValid($user, $credentials['password']);
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        if (!$token instanceof AnonymousToken && $this->anonymousFollowerSession->isStarted()) {
            return $this->anonymousFollowerSession->terminate();
        }

        if ($targetPath = $this->getTargetPath($request->getSession(), $providerKey)) {
            return new RedirectResponse($targetPath);
        }

        return new RedirectResponse($this->urlGenerator->generate('app_search_events'));
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        $this->failedLoginAttemptRepository->save(FailedLoginAttempt::createFromRequest($request));

        return parent::onAuthenticationFailure($request, $exception);
    }

    public function start(Request $request, AuthenticationException $authException = null)
    {
        if (
            ($request->headers->has('Accept') && 'application/json' === $request->headers->get('Accept'))
            || preg_match('#^/api/#', $request->getPathInfo())
        ) {
            return new JsonResponse('Unauthorized', 401);
        }

        return parent::start($request, $authException);
    }

    protected function getLoginUrl()
    {
        return $this->urlGenerator->generate('app_user_login');
    }
}
