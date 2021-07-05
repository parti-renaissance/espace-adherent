<?php

namespace App\VotingPlatform\Election;

use App\Entity\VotingPlatform\Election;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class RedirectManager
{
    private $urlGenerator;

    public function __construct(UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }

    public function getRedirection(Election $election): string
    {
        $designation = $election->getDesignation();

        if ($designation->isCommitteeType()) {
            return $this->urlGenerator->generate(
                'app_committee_show',
                ['slug' => $election->getElectionEntity()->getCommittee()->getSlug()]
            );
        }

        if ($designation->isCopolType()) {
            return $this->urlGenerator->generate('app_territorial_council_index');
        }

        if ($designation->isExecutiveOfficeType()) {
            return $this->urlGenerator->generate('app_national_council_index');
        }

        return $this->urlGenerator->generate('homepage');
    }
}
