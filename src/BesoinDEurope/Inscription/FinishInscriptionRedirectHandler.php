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

    public function redirectToCompleteInscription(?string $initialTargetUrl): ?RedirectResponse
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

        if ($initialTargetUrl) {
            $this->session->set(self::SESSION_KEY, $initialTargetUrl);
        }

        return new RedirectResponse($this->urlGenerator->generate($nextStepRouteName));
    }
}
