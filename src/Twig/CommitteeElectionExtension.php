<?php

declare(strict_types=1);

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class CommitteeElectionExtension extends AbstractExtension
{
    public function getFunctions()
    {
        return [
            new TwigFunction('is_candidate', [CommitteeRuntime::class, 'isCandidate']),
            new TwigFunction('count_committee_candidates', [CommitteeRuntime::class, 'countCommitteeCandidates']),
        ];
    }
}
