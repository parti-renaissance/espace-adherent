<?php

declare(strict_types=1);

namespace App\History;

use App\Entity\Administrator;
use App\Entity\Committee;
use Symfony\Contracts\EventDispatcher\Event;

class AdministratorCommitteeActionEvent extends Event
{
    public function __construct(
        public readonly Administrator $administrator,
        public readonly ?Committee $committee = null,
    ) {
    }
}
