<?php

declare(strict_types=1);

namespace App\VotingPlatform\AdherentMandate;

use App\Entity\AdherentMandate\AdherentMandateInterface;
use App\Entity\VotingPlatform\Candidate;
use App\Entity\VotingPlatform\Election;
use App\VotingPlatform\AdherentMandate\Factory\AdherentMandateFactoryInterface;

class AdherentMandateFactory
{
    /** @var AdherentMandateFactoryInterface[] */
    private $factories;

    public function __construct(iterable $factories)
    {
        $this->factories = $factories;
    }

    public function create(Election $election, Candidate $candidate, string $quality): AdherentMandateInterface
    {
        foreach ($this->factories as $factory) {
            if ($factory->supports($election)) {
                return $factory->create($election, $candidate, $quality);
            }
        }

        throw new \RuntimeException('Mandate factory is not found');
    }
}
