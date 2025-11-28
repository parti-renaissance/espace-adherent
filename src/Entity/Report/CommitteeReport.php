<?php

declare(strict_types=1);

namespace App\Entity\Report;

use App\Entity\Committee;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class CommitteeReport extends Report
{
    /**
     * @var Committee
     */
    #[ORM\JoinColumn(name: 'committee_id', onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Committee::class)]
    protected $subject;
}
