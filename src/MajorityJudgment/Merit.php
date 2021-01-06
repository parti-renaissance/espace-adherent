<?php

namespace App\MajorityJudgment;

class Merit
{
    private $mention;
    private $percent;
    private $isReset = false;

    public function __construct(Mention $mention, float $percent)
    {
        $this->mention = $mention;
        $this->percent = $percent;
    }

    public function getMention(): Mention
    {
        return $this->mention;
    }

    public function getPercent(): float
    {
        return $this->percent;
    }

    public function reset(): void
    {
        $this->isReset = true;
    }

    public function isReset(): bool
    {
        return $this->isReset;
    }
}
