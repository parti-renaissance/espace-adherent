<?php

declare(strict_types=1);

namespace App\Security\Http\LoginLink;

use App\AppCodeEnum;
use App\OAuth\App\AuthAppUrlManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\LoginLink\LoginLinkDetails;
use Symfony\Component\Security\Http\LoginLink\LoginLinkHandlerInterface;

class LoginLinkHandler implements LoginLinkHandlerInterface
{
    public function __construct(
        private readonly LoginLinkHandlerInterface $decorated,
        private readonly AuthAppUrlManager $appUrlManager,
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function createLoginLink(UserInterface $user, ?Request $request = null, ?int $lifetime = null, ?string $appCode = null, ?string $targetPath = null): LoginLinkDetails
    {
        $link = $this->decorated->createLoginLink($user, $request, $lifetime);

        $queryParams = [
            '_failure_path' => '/connexion',
            '_target_path' => $targetPath,
        ];

        if (AppCodeEnum::isMobileApp($appCode)) {
            if (!$request || !$queryParams['_target_path'] = $request->getSession()->get('_security.main.target_path')) {
                $queryParams['_target_path'] = $this->urlGenerator->generate('vox_app_redirect');
            }
            $host = $this->appUrlManager->getUrlGenerator($appCode)->getAppHost();
        }

        $urlParts = parse_url($link->getUrl().'&'.http_build_query($queryParams));

        return new LoginLinkDetails(
            \sprintf(
                '%s://%s%s?%s',
                $urlParts['scheme'],
                $host ?? $urlParts['host'],
                $urlParts['path'],
                $urlParts['query']
            ),
            $link->getExpiresAt()
        );
    }

    public function consumeLoginLink(Request $request): UserInterface
    {
        return $this->decorated->consumeLoginLink($request);
    }
}
