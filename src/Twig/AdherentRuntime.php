<?php

namespace App\Twig;

use App\Entity\Adherent;
use App\Entity\ReferentSpaceAccessInformation;
use App\Repository\ReferentSpaceAccessInformationRepository;
use Twig\Extension\RuntimeExtensionInterface;

class AdherentRuntime implements RuntimeExtensionInterface
{
    private $memberInterests;
    private $accessInformationRepository;

    public function __construct(ReferentSpaceAccessInformationRepository $accessInformationRepository, array $interests)
    {
        $this->accessInformationRepository = $accessInformationRepository;
        $this->memberInterests = $interests;
    }

    public function getMemberInterestLabel(string $interest)
    {
        if (!isset($this->memberInterests[$interest])) {
            return '';
        }

        return $this->memberInterests[$interest];
    }

    public function getUserLevelLabel(Adherent $adherent): string
    {
        if (!$adherent->isAdherent()) {
            return 'Non-adhérent(e)';
        }

        if ($adherent->isReferent()) {
            return $adherent->isFemale() ? 'Référente 🥇' : 'Référent 🥇';
        }

        if ($adherent->isCoReferent()) {
            return 'Équipe du référent 🥈';
        }

        if ($adherent->isDeputy()) {
            return $adherent->isFemale() ? 'Députée 🏛' : 'Député 🏛';
        }

        if ($adherent->isSupervisor()) {
            return $adherent->isFemale() ? 'Animatrice 🏅' : 'Animateur 🏅';
        }

        if ($adherent->isHost()) {
            return $adherent->isFemale() ? 'Co-animatrice 🏅' : 'Co-animateur 🏅';
        }

        // It means the user is an adherent
        return $adherent->isFemale() ? 'Adhérente 😍' : 'Adhérent 😍';
    }

    public function getAdherentRoleLabels(Adherent $adherent): array
    {
        $labels = [];

        if ($adherent->isAdherent()) {
            $labels[] = $adherent->isFemale() ? 'Adhérente 😍' : 'Adhérent 😍';
        } else {
            $labels[] = 'Non-adhérent(e)';
        }

        if ($adherent->isReferent()) {
            $labels[] = $adherent->isFemale() ? 'Référente 🥇' : 'Référent 🥇';
        }

        if ($adherent->isDelegatedReferent()) {
            $labels[] = $adherent->isFemale() ? 'Référente déléguée 🥇' : 'Référent délégué 🥇';
        }

        if ($adherent->isCoReferent()) {
            $labels[] = 'Équipe du référent 🥈';
        }

        if ($adherent->isDeputy()) {
            $labels[] = $adherent->isFemale() ? 'Députée 🏛' : 'Député 🏛';
        }

        if ($adherent->isDelegatedDeputy()) {
            $labels[] = $adherent->isFemale() ? 'Députée déléguée 🏛' : 'Député délégué 🏛';
        }

        if ($adherent->isSenator()) {
            $labels[] = $adherent->isFemale() ? 'Sénatrice 🏛' : 'Sénateur 🏛';
        }

        if ($adherent->isDelegatedSenator()) {
            $labels[] = $adherent->isFemale() ? 'Sénatrice déléguée 🏛' : 'Sénateur délégué 🏛';
        }

        if ($adherent->isSupervisor()) {
            $labels[] = $adherent->isFemale() ? 'Animatrice 🏅' : 'Animateur 🏅';
        }

        if ($adherent->isHost()) {
            $labels[] = $adherent->isFemale() ? 'Co-animatrice 🏅' : 'Co-animateur 🏅';
        }

        if ($adherent->isMunicipalChief()) {
            $labels[] = 'Candidat Municipales 2020 🇫🇷';
        }

        return $labels;
    }

    public function getReferentPreviousVisitDate(Adherent $adherent): ?\DateTimeInterface
    {
        /** @var ReferentSpaceAccessInformation $accessInformation */
        $accessInformation = $this->accessInformationRepository->findByAdherent($adherent, 7200);

        if ($accessInformation) {
            return $accessInformation->getPreviousDate();
        }

        return null;
    }
}
