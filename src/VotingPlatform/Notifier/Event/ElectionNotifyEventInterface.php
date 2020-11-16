<?php

namespace App\VotingPlatform\Notifier\Event;

use App\Entity\Adherent;
use App\Entity\VotingPlatform\Designation\Designation;

interface ElectionNotifyEventInterface
{
    public function getAdherent(): Adherent;

    public function getDesignation(): Designation;
}
