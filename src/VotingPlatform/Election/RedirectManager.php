<?php

namespace AppBundle\VotingPlatform\Election;

use AppBundle\Entity\VotingPlatform\Election;
use AppBundle\VotingPlatform\Designation\DesignationTypeEnum;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class RedirectManager
{
    private $urlGenerator;

    public function __construct(UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }

    public function getRedirection(Election $election): ?string
    {
        switch ($election->getDesignationType()) {
            case DesignationTypeEnum::COMMITTEE_ADHERENT:
                return $this->urlGenerator->generate(
                    'app_committee_show',
                    ['slug' => $election->getElectionEntity()->getCommittee()->getSlug()]
                );
        }

        return $this->urlGenerator->generate('app_homepage');
    }
}
