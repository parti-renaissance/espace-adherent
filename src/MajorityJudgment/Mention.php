<?php

namespace App\MajorityJudgment;

class Mention
{
    /**
     * @var int|null
     */
    private $index;
    private $value;

    public function __construct(string $value)
    {
        $this->value = $value;
    }

    public function getIndex(): ?int
    {
        return $this->index;
    }

    public function setIndex(int $index): void
    {
        $this->index = $index;
    }

    public function getValue(): string
    {
        return $this->value;
    }
}
