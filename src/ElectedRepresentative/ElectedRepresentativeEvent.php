<?php

declare(strict_types=1);

namespace App\ElectedRepresentative;

use App\Entity\ElectedRepresentative\ElectedRepresentative;
use Symfony\Contracts\EventDispatcher\Event;

class ElectedRepresentativeEvent extends Event
{
    private $electedRepresentative;

    public function __construct(ElectedRepresentative $electedRepresentative)
    {
        $this->electedRepresentative = $electedRepresentative;
    }

    public function getElectedRepresentative(): ElectedRepresentative
    {
        return $this->electedRepresentative;
    }
}
