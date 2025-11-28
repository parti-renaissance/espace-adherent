<?php

declare(strict_types=1);

namespace App\Committee\Event;

use App\Entity\Committee;

interface CommitteeEventInterface
{
    public function getCommittee(): Committee;
}
