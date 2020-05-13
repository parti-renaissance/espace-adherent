<?php

namespace App\CitizenProject;

use App\Entity\Adherent;
use App\Entity\CitizenProject;

class CitizenProjectFollowerChangeEvent extends CitizenProjectEvent
{
    private $follower;

    public function __construct(CitizenProject $citizenProject, Adherent $follower)
    {
        parent::__construct($citizenProject);

        $this->follower = $follower;
    }

    public function getFollower(): Adherent
    {
        return $this->follower;
    }
}
