<?php

namespace App\JeMengage\Alert\Provider;

use App\Entity\Adherent;
use App\JeMengage\Alert\Alert;
use App\VotingPlatform\Designation\DesignationTypeEnum;
use App\VotingPlatform\ElectionManager;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ElectionAlertProvider implements AlertProviderInterface
{
    public function __construct(
        private readonly ElectionManager $electionManager,
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function getAlert(Adherent $adherent): ?Alert
    {
        $designations = $this->electionManager->findActiveDesignations(
            $adherent,
            DesignationTypeEnum::NATIONAL_TYPES,
        );

        if (!$designations) {
            return null;
        }

        foreach ($designations as $designation) {
            if ($designation->alertTitle && $designation->alertDescription) {
                return new Alert(
                    $designation->alertTitle,
                    $designation->alertDescription,
                    $designation->alertCtaLabel,
                    $this->urlGenerator->generate('app_sas_election_index', ['uuid' => $designation->getUuid()], UrlGeneratorInterface::ABSOLUTE_URL)
                );
            }
        }

        return null;
    }
}
