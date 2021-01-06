<?php

namespace App\MajorityJudgment;

class Merit
{
    private $mention;
    private $percent;

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
}
