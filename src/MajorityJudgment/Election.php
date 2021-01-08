<?php

namespace App\MajorityJudgment;

class Election
{
    /**
     * @var Mention[]
     */
    private $mentions = [];

    /**
     * @var Candidate[]
     */
    private $candidates = [];

    /**
     * @var VotingProfile[]
     */
    private $votingProfiles = [];

    /**
     * @var array
     */
    private $result;

    public function __construct(array $mentions)
    {
        if (empty($mentions)) {
            throw new \InvalidArgumentException('Mentions array cannot be empty.');
        }

        array_walk($mentions, [$this, 'addMention']);
    }

    public function addMention(Mention $mention): void
    {
        $this->mentions[] = $mention;
        $mention->setIndex(\count($this->mentions) - 1);
    }

    public function addCandidate(Candidate $candidate): void
    {
        $this->candidates[] = $candidate;
    }

    public function findMention(int $index): ?Mention
    {
        return $this->mentions[$index] ?? null;
    }

    public function findCandidate(string $identifier): ?Candidate
    {
        foreach ($this->candidates as $candidate) {
            if ($candidate->getIdentifier() === $identifier) {
                return $candidate;
            }
        }

        return null;
    }

    /**
     * @return Candidate[]
     */
    public function getCandidates(): array
    {
        return $this->candidates;
    }

    /**
     * @return VotingProfile[]
     */
    public function getVotingProfiles(): array
    {
        return $this->votingProfiles;
    }

    /**
     * @param Mention[] $mentions
     * @param string[]  $candidateIdentifiers
     */
    public static function createWithVotingProfiles(
        array $mentions,
        array $candidateIdentifiers,
        array $votingProfiles
    ): self {
        $election = new self($mentions);

        // init candidates
        foreach ($candidateIdentifiers as $identifier) {
            $election->addCandidate(new Candidate($identifier));
        }

        // init votes
        $total = null;
        foreach ($votingProfiles as $candidateIdentifier => $mentionRow) {
            if (null === $total) {
                $total = array_sum($mentionRow);
            } elseif ($total !== array_sum($mentionRow)) {
                throw new \RuntimeException('Vote count is not identical between the candidates.');
            }

            $candidate = $election->findCandidate($candidateIdentifier);
            $election->addVotingProfile($votingProfile = new VotingProfile($candidate));

            foreach ($mentionRow as $mentionIndex => $count) {
                $votingProfile->addMerit(new Merit(
                    $election->findMention($mentionIndex),
                    $count * 100.0 / $total
                ));
            }
        }

        return $election;
    }

    private function addVotingProfile(VotingProfile $votingProfile): void
    {
        $this->votingProfiles[] = $votingProfile;
    }

    public function setResult(array $result): void
    {
        $this->result = $result;
    }

    public function getWinner(): ?Candidate
    {
        if (empty($this->result)) {
            return null;
        }

        /** @var Candidate[] $majorityMentionCandidates */
        $majorityMentionCandidates = current($this->result);

        foreach ($majorityMentionCandidates as $candidate) {
            if ($candidate->isElected()) {
                return $candidate;
            }
        }

        return null;
    }
}
