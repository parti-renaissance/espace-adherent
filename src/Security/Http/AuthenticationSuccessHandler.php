<?php

declare(strict_types=1);

namespace App\Security\Http;

use App\Entity\Adherent;
use App\Routing\AppDomainParamsTrait;
use App\Security\Http\Session\AnonymousFollowerSession;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\NullToken;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authentication\DefaultAuthenticationSuccessHandler;
use Symfony\Contracts\Service\Attribute\Required;

class AuthenticationSuccessHandler extends DefaultAuthenticationSuccessHandler
{
    use AppDomainParamsTrait;

    private AnonymousFollowerSession $anonymousFollowerSession;
    private EntityManagerInterface $manager;
    private UrlGeneratorInterface $urlGenerator;

    public function onAuthenticationSuccess(Request $request, TokenInterface $token): ?Response
    {
        if (!$token instanceof NullToken && $this->anonymousFollowerSession->isStarted()) {
            return $this->anonymousFollowerSession->terminate();
        }

        $user = $token->getUser();

        // Only record adherent logins
        if ($user instanceof Adherent) {
            $user->recordLastLoginTime();
            $this->manager->flush();
        }

        return parent::onAuthenticationSuccess($request, $token);
    }

    protected function determineTargetUrl(Request $request): string
    {
        $targetUrl = parent::determineTargetUrl($request);

        $usedDefaultTarget = $targetUrl === ($this->options['default_target_path'] ?? null);

        $this->logger->info('OAuth login success target resolution', [
            'used_default_target' => $usedDefaultTarget,
            'target' => $targetUrl,
            'user_agent' => $request->headers->get('User-Agent'),
            'host' => $request->getHost(),
        ]);

        // The default target is a route name (e.g. "vox_app_redirect") whose host defaults to the vox
        // host. Regenerate it on the host the user logged in from so the /app -> /auth OAuth hop stays
        // on the same domain (campaign users must not be bounced to the vox host).
        if ($usedDefaultTarget) {
            return $this->urlGenerator->generate($targetUrl, $this->appDomainParams($request));
        }

        return $targetUrl;
    }

    #[Required]
    public function setUrlGenerator(UrlGeneratorInterface $urlGenerator): void
    {
        $this->urlGenerator = $urlGenerator;
    }

    #[Required]
    public function setAnonymousFollowerSession(AnonymousFollowerSession $anonymousFollowerSession): void
    {
        $this->anonymousFollowerSession = $anonymousFollowerSession;
    }

    #[Required]
    public function setManager(EntityManagerInterface $manager): void
    {
        $this->manager = $manager;
    }

    #[Required]
    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }
}
