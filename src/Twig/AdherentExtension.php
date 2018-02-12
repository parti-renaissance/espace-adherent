<?php

namespace AppBundle\Twig;

use AppBundle\Entity\Adherent;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class AdherentExtension extends AbstractExtension
{
    private $memberInterests;
    private $unregistrationReasons;

    public function __construct(array $interests, array $unregistrationReasons)
    {
        $this->memberInterests = $interests;
        $this->unregistrationReasons = $unregistrationReasons;
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('member_interest_label', [$this, 'getMemberInterestLabel']),
            new TwigFunction('unregistration_reasons_label', [$this, 'getUnregistrationReasonsLabel']),
            new TwigFunction('get_user_level_label', [$this, 'getUserLevelLabel']),
        ];
    }

    public function getMemberInterestLabel(string $interest)
    {
        if (!isset($this->memberInterests[$interest])) {
            return '';
        }

        return $this->memberInterests[$interest];
    }

    public function getUnregistrationReasonsLabel(string $reasons)
    {
        if (!isset($this->unregistrationReasons[$reasons])) {
            return '';
        }

        return $this->unregistrationReasons[$reasons];
    }

    public function getUserLevelLabel(Adherent $adherent): string
    {
        if (!$adherent->isAdherent()) {
            return 'Non-adhérent(e)';
        }

        if ($adherent->isReferent()) {
            return $adherent->isFemale() ? 'Référente 🥇' : 'Référent 🥇';
        }

        if ($adherent->isHost()) {
            return $adherent->isFemale() ? 'Animatrice 🏅' : 'Animateur 🏅';
        }

        // It means the user is an adherent
        return $adherent->isFemale() ? 'Adhérente 😍' : 'Adhérent 😍';
    }
}
