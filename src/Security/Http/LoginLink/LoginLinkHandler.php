<?php

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

    public function createLoginLink(UserInterface $user, ?Request $request = null, ?string $appCode = null): LoginLinkDetails
    {
        $link = $this->decorated->createLoginLink($user, $request);

        if (AppCodeEnum::isBesoinDEuropeApp($appCode)) {
            if (!$request || !$targetPath = $request->getSession()->get('_security.main.target_path')) {
                $targetPath = $this->urlGenerator->generate('vox_app_redirect');
            }
            $urlParts = parse_url($link->getUrl().'&_failure_path=/connexion&_target_path='.urlencode($targetPath));

            return new LoginLinkDetails(
                sprintf(
                    '%s://%s%s?%s',
                    $urlParts['scheme'],
                    $this->appUrlManager->getUrlGenerator($appCode)->getAppHost(),
                    $urlParts['path'],
                    $urlParts['query']
                ),
                $link->getExpiresAt()
            );
        }

        return $link;
    }

    public function consumeLoginLink(Request $request): UserInterface
    {
        return $this->decorated->consumeLoginLink($request);
    }
}
