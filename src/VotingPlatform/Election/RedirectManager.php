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

        if ($designation->isRenaissanceElection()) {
            return $this->urlGenerator->generate('app_renaissance_adherent_space');
        }

        if ($designation->isCommitteeTypes()) {
            return $this->urlGenerator->generate(
                'app_committee_show',
                ['slug' => $election->getElectionEntity()->getCommittee()->getSlug()]
            );
        }

        return $this->urlGenerator->generate('homepage');
    }
}
