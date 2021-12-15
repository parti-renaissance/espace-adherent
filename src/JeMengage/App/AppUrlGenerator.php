<?php

namespace App\JeMengage\App;

use App\Entity\Adherent;
use App\Entity\AdherentExpirableTokenInterface;
use App\Membership\MembershipSourceEnum;
use App\OAuth\App\AbstractAppUrlGenerator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class AppUrlGenerator extends AbstractAppUrlGenerator
{
    private string $appHost;
    private string $appAuthHost;

    public function __construct(UrlGeneratorInterface $urlGenerator, string $jemengageHost, string $jemengageAuthHost)
    {
        parent::__construct($urlGenerator);

        $this->appHost = $jemengageHost;
        $this->appAuthHost = $jemengageAuthHost;
    }

    public static function getAppCode(): string
    {
        return MembershipSourceEnum::JEMENGAGE;
    }

    public function guessAppCodeFromRequest(Request $request): ?string
    {
        if ($request->attributes->get('app_domain') === $this->appAuthHost) {
            return static::getAppCode();
        }

        return null;
    }

    public function generateHomepageLink(): string
    {
        return $this->appHost;
    }

    public function generateLoginLink(): string
    {
        return $this->urlGenerator->generate('app_jemengage_login');
    }

    public function generateCreatePasswordLink(Adherent $adherent, AdherentExpirableTokenInterface $token): string
    {
        return sprintf('%s/confirmation/%s/%s', $this->appHost, $adherent->getUuid(), $token->getValue());
    }
}
