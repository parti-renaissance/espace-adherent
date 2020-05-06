<?php

namespace App\Election;

use App\Entity\Election\VoteResultList;

class ListTotalResult
{
    private $list;

    private $total;

    public function __construct(VoteResultList $list, int $total)
    {
        $this->list = $list;
        $this->total = $total;
    }

    public function getList(): VoteResultList
    {
        return $this->list;
    }

    public function getTotal(): int
    {
        return $this->total;
    }

    public function addTotal(int $total): void
    {
        $this->total += $total;
    }
}
