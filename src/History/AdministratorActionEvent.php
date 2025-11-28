<?php

declare(strict_types=1);

namespace App\History;

use App\Entity\Adherent;
use App\Entity\Administrator;
use Symfony\Contracts\EventDispatcher\Event;

class AdministratorActionEvent extends Event
{
    public function __construct(
        public readonly Administrator $administrator,
        public readonly ?Adherent $adherent = null,
    ) {
    }
}
