<?php

namespace App\Committee\Event;

use App\Entity\Committee;

interface CommitteeEventInterface
{
    public function getCommittee(): Committee;
}
