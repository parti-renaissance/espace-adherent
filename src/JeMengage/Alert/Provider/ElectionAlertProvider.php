<?php

namespace App\JeMengage\Alert\Provider;

use App\Entity\Adherent;
use App\JeMengage\Alert\Alert;
use App\VotingPlatform\Designation\DesignationTypeEnum;
use App\VotingPlatform\ElectionManager;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\LoginLink\LoginLinkHandlerInterface;

class ElectionAlertProvider implements AlertProviderInterface
{
    public function __construct(
        private readonly ElectionManager $electionManager,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly LoginLinkHandlerInterface $loginLinkHandler,
    ) {
    }

    public function getAlerts(Adherent $adherent): array
    {
        $designations = $this->electionManager->findActiveDesignations(
            $adherent,
            [
                DesignationTypeEnum::LOCAL_POLL,
                DesignationTypeEnum::CONSULTATION,
                DesignationTypeEnum::VOTE,
                DesignationTypeEnum::CONGRESS_CN,
            ],
        );

        if (!$designations) {
            return [];
        }

        $alerts = [];

        foreach ($designations as $designation) {
            if ($designation->alertTitle && $designation->alertDescription) {
                $alerts[] = Alert::createElection(
                    $designation->alertTitle,
                    $designation->getFullAlertDescription(),
                    $designation->alertCtaLabel,
                    $this->loginLinkHandler->createLoginLink(
                        $adherent,
                        targetPath: $this->urlGenerator->generate('app_sas_election_index', ['uuid' => $designation->getUuid()], UrlGeneratorInterface::ABSOLUTE_URL)
                    )
                );
            }
        }

        return $alerts;
    }
}
