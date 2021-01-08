<?php

namespace App\MajorityJudgment;

class Candidate
{
    /**
     * @var string
     */
    private $identifier;

    /**
     * @var Mention|null
     */
    private $majorityMention;

    /**
     * @var bool
     */
    private $isElected = false;

    public function __construct(string $identifier)
    {
        $this->identifier = $identifier;
    }

    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    public function getMajorityMention(): ?Mention
    {
        return $this->majorityMention;
    }

    public function setMajorityMention(Mention $majorityMention): void
    {
        $this->majorityMention = $majorityMention;
    }

    public function markElected(): void
    {
        $this->isElected = true;
    }

    public function isElected(): bool
    {
        return $this->isElected;
    }
}
