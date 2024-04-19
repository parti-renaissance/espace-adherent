<?php

namespace App\BesoinDEurope\Inscription;

use App\Adhesion\AdhesionStepEnum;
use App\Entity\Adherent;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Security;

class FinishInscriptionRedirectHandler
{
    public const SESSION_KEY = 'besoin_d_europe.redirect.target_url';

    public function __construct(
        private readonly Security $security,
        private readonly SessionInterface $session,
        private readonly UrlGeneratorInterface $urlGenerator
    ) {
    }

    public function getBesoinDEuropeRedirect(?string $targetUrl): ?RedirectResponse
    {
        $adherent = $this->security->getUser();

        if (
            !$adherent instanceof Adherent
            || !$adherent->isBesoinDEuropeUser()
            || $adherent->isFullyCompletedBesoinDEuropeInscription()
        ) {
            return null;
        }

        $nextStepRouteName = AdhesionStepEnum::getBesoinDEuropeNextStep($adherent->getFinishedAdhesionSteps());

        if (!$nextStepRouteName) {
            return null;
        }

        if ($targetUrl) {
            $this->session->set(self::SESSION_KEY, $targetUrl);
        }

        return new RedirectResponse($this->urlGenerator->generate($nextStepRouteName));
    }
}
