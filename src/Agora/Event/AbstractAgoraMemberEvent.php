<?php

declare(strict_types=1);

namespace App\Agora\Event;

use App\Entity\Administrator;
use App\Entity\AgoraMembership;
use Symfony\Contracts\EventDispatcher\Event;

abstract class AbstractAgoraMemberEvent extends Event
{
    public function __construct(
        public readonly AgoraMembership $agoraMembership,
        public readonly ?Administrator $admin = null,
    ) {
    }
}
