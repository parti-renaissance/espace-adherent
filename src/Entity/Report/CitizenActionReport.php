<?php

namespace App\Entity\Report;

use App\Entity\CitizenAction;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class CitizenActionReport extends Report
{
    /**
     * @var CitizenAction
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\CitizenAction")
     * @ORM\JoinColumn(name="citizen_action_id")
     */
    protected $subject;
}
