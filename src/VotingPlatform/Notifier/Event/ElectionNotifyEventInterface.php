<?php

namespace App\VotingPlatform\Notifier\Event;

use App\Entity\Adherent;
use App\Entity\VotingPlatform\Election;

interface ElectionNotifyEventInterface
{
    public function getAdherent(): Adherent;

    public function getElection(): Election;
}
