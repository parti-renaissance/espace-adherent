<?php

declare(strict_types=1);

namespace App\VotingPlatform\Election\ResultCalculator;

use App\Entity\VotingPlatform\CandidateGroup;
use App\Entity\VotingPlatform\Designation\Designation;
use App\Entity\VotingPlatform\ElectionResult\ElectionPoolResult;
use App\VotingPlatform\Designation\MajorityVoteMentionEnum;
use EnMarche\MajorityJudgment\Election;
use EnMarche\MajorityJudgment\Mention;
use EnMarche\MajorityJudgment\Processor;

class MajorityJudgmentCalculator extends AbstractResultCalculator
{
    public function support(Designation $designation): bool
    {
        return $designation->isMajorityType();
    }

    protected function calculateForPool(ElectionPoolResult $electionPoolResult): ?CandidateGroup
    {
        $elected = null;
        $candidatesIdentifiers = $votingProfiles = [];

        foreach ($electionPoolResult->getCandidateGroupResults() as $result) {
            $candidatesIdentifiers[] = $id = $result->getCandidateGroup()->getId();
            $votingProfiles[$id] = array_map(function (string $mention) use ($result) {
                return $result->getTotalMentions()[$mention] ?? 0;
            }, MajorityVoteMentionEnum::ALL);
        }

        $election = Election::createWithVotingProfiles(
            array_map(function (string $mention) { return new Mention($mention); }, MajorityVoteMentionEnum::ALL),
            $candidatesIdentifiers,
            $votingProfiles
        );

        Processor::process($election);

        foreach ($electionPoolResult->getCandidateGroupResults() as $result) {
            $candidate = $election->findCandidate((string) $result->getCandidateGroup()->getId());
            if ($candidate->getMajorityMention()) {
                $result->setMajorityMention($candidate->getMajorityMention()->getValue());
            }

            if ($candidate->isElected()) {
                $elected = $result->getCandidateGroup();
            }
        }

        return $elected;
    }
}
