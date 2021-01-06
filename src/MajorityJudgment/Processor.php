<?php

namespace App\MajorityJudgment;

final class Processor
{
    public static function process(Election $election): void
    {
        $votingProfiles = $election->getVotingProfiles();

        $result = [];

        foreach ($election->getCandidates() as $candidate) {
            // Find all votingProfiles for the candidate
            for ($votingProfile = current($votingProfiles); $votingProfile->getCandidate() !== $candidate; $votingProfile = next($votingProfiles));

            $totalPercent = 0.0;
            $majorityMention = null;

            foreach ($votingProfile->getMerits() as $merit) {
                $totalPercent += $merit->getPercent();

                if ($totalPercent > 50) {
                    $majorityMention = $merit->getMention();

                    if (!isset($result[$majorityMention->getIndex()])) {
                        $result[$majorityMention->getIndex()] = [];
                    }

                    $result[$majorityMention->getIndex()][] = $candidate;

                    break;
                }
            }
        }

        $election->setResult($result);
    }
}
