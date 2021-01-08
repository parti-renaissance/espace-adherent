<?php

namespace App\MajorityJudgment;

final class Processor
{
    public static function process(Election $election): void
    {
        $votingProfiles = $election->getVotingProfiles();

        $result = [];

        // 1. Calculate majority mention for each candidate
        foreach ($election->getCandidates() as $candidate) {
            // Find candidate's votingProfile
            if (!$votingProfile = self::findVotingProfile($candidate, $votingProfiles)) {
                continue;
            }

            $totalPercent = 0.0;
            $majorityMention = null;

            foreach ($votingProfile->getMerits() as $merit) {
                $totalPercent += $merit->getPercent();

                if ($totalPercent > 50) {
                    $majorityMention = $merit->getMention();

                    if (!isset($result[$majorityMention->getIndex()])) {
                        $result[$majorityMention->getIndex()] = [];
                    }

                    $candidate->setMajorityMention($majorityMention);
                    $result[$majorityMention->getIndex()][] = $candidate;

                    break;
                }
            }
        }

        $election->setResult($result);

        // 2. Find winner
        self::processWinner(current($result), $votingProfiles);
    }

    private static function processWinner(array $equalsCandidates, array $votingProfiles): void
    {
        if (!$equalsCandidates) {
            return;
        }

        if (1 === \count($equalsCandidates)) {
            current($equalsCandidates)->markElected();

            return;
        }

        [$maxProponentsPercent, $maxOpponentsPercent, $maxProponentsCandidates, $maxOpponentsCandidates] = self::calculateProponentsOpponents($equalsCandidates, $votingProfiles);

        if ($maxProponentsPercent > $maxOpponentsPercent) {
            foreach ($equalsCandidates as $index => $candidate) {
                if (!\in_array($candidate, $maxProponentsCandidates)) {
                    unset($equalsCandidates[$index]);
                }
            }

            if (\count($equalsCandidates) > 1) {
                self::resetPreviousMerit($equalsCandidates, $votingProfiles);
            }
        } elseif ($maxProponentsPercent < $maxOpponentsPercent) {
            foreach ($equalsCandidates as $index => $candidate) {
                if (\in_array($candidate, $maxOpponentsCandidates)) {
                    unset($equalsCandidates[$index]);
                }
            }
        } else {
            if ($maxOpponentsCandidates == $maxProponentsCandidates || 1 === \count($maxOpponentsCandidates)) {
                foreach ($equalsCandidates as $index => $candidate) {
                    if (\in_array($candidate, $maxOpponentsCandidates)) {
                        unset($equalsCandidates[$index]);
                    }
                }
            } elseif (\count($equalsCandidates) > 1) {
                self::resetPreviousMerit($equalsCandidates, $votingProfiles, true);
            }
        }

        self::processWinner($equalsCandidates, $votingProfiles);
    }

    /**
     * @param Candidate[]     $equalsCandidates
     * @param VotingProfile[] $votingProfiles
     */
    private static function calculateProponentsOpponents(array $equalsCandidates, array $votingProfiles): array
    {
        $maxProponentsPercent = $maxOpponentsPercent = null;
        $maxProponentsCandidates = $maxOpponentsCandidates = [];

        foreach ($equalsCandidates as $candidate) {
            if (!$votingProfile = self::findVotingProfile($candidate, $votingProfiles)) {
                continue;
            }

            $totalPercent = 0.0;

            foreach ($votingProfile->getMerits() as $merit) {
                if ($merit->getMention() === $candidate->getMajorityMention()) {
                    if ($maxProponentsPercent <= $totalPercent) {
                        if ($maxProponentsPercent === $totalPercent) {
                            $maxProponentsCandidates[] = $candidate;
                        } else {
                            $maxProponentsCandidates = [$candidate];
                        }
                        $maxProponentsPercent = $totalPercent;
                    }

                    $totalPercent = 0.0;

                    continue;
                }

                if (!$merit->isReset()) {
                    $totalPercent += $merit->getPercent();
                }
            }

            if ($maxOpponentsPercent <= $totalPercent) {
                if ($maxOpponentsPercent === $totalPercent) {
                    $maxOpponentsCandidates[] = $candidate;
                } else {
                    $maxOpponentsCandidates = [$candidate];
                }
                $maxOpponentsPercent = $totalPercent;
            }
        }

        return [
            $maxProponentsPercent,
            $maxOpponentsPercent,
            $maxProponentsCandidates,
            $maxOpponentsCandidates,
        ];
    }

    /**
     * @param VotingProfile[] $votingProfiles
     */
    private static function findVotingProfile(Candidate $candidate, array $votingProfiles): ?VotingProfile
    {
        foreach ($votingProfiles as $votingProfile) {
            if ($votingProfile->getCandidate() === $candidate) {
                return $votingProfile;
            }
        }

        return null;
    }

    private static function resetPreviousMerit(
        array $equalsCandidates,
        array $votingProfiles,
        bool $reverse = false
    ): void {
        foreach ($equalsCandidates as $candidate) {
            if ($votingProfile = self::findVotingProfile($candidate, $votingProfiles)) {
                $previousMerit = null;
                foreach ($reverse ? array_reverse($votingProfile->getMerits()) : $votingProfile->getMerits() as $merit) {
                    if ($merit->getMention() === $candidate->getMajorityMention()) {
                        if (!$previousMerit) {
                            continue;
                        }
                        $previousMerit->reset();
                        break;
                    }

                    if (!$merit->isReset()) {
                        $previousMerit = $merit;
                    }
                }
            }
        }
    }
}
