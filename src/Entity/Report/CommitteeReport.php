<?php

namespace App\Entity\Report;

use App\Entity\Committee;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class CommitteeReport extends Report
{
    /**
     * @var Committee
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Committee")
     * @ORM\JoinColumn(name="committee_id")
     */
    protected $subject;
}
