<?php

namespace AppBundle\Entity\Reporting;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use AppBundle\Entity\Administrator;
use AppBundle\Entity\Committee;
use Cake\Chronos\Chronos;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(
 *     name="committee_merge_histories",
 *     indexes={
 *         @ORM\Index(name="committee_merge_histories_source_committee_id_idx", columns="source_committee_id"),
 *         @ORM\Index(name="committee_merge_histories_destination_committee_id_idx", columns="destination_committee_id"),
 *         @ORM\Index(name="committee_merge_histories_date_idx", columns="date")
 *     }
 * )
 * @ORM\Entity
 *
 * @Algolia\Index(autoIndex=false)
 */
class CommitteeMergeHistory
{
    /**
     * @var int|null
     *
     * @ORM\Id
     * @ORM\Column(type="integer", options={"unsigned": true})
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var Committee
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Committee")
     * @ORM\JoinColumn(nullable=false)
     */
    private $sourceCommittee;

    /**
     * @var Committee
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Committee")
     * @ORM\JoinColumn(nullable=false)
     */
    private $destinationCommittee;

    /**
     * @var string
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Administrator")
     */
    private $mergedBy;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime_immutable")
     */
    private $date;

    public function __construct(
        Committee $sourceCommittee,
        Committee $destinationCommittee,
        Administrator $admin,
        \DateTimeImmutable $date = null
    ) {
        $this->sourceCommittee = $sourceCommittee;
        $this->destinationCommittee = $destinationCommittee;
        $this->mergedBy = $admin;
        $this->date = $date ?: new Chronos();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSourceCommittee(): Committee
    {
        return $this->sourceCommittee;
    }

    public function getDestinationCommittee(): Committee
    {
        return $this->destinationCommittee;
    }

    public function getDate(): \DateTimeImmutable
    {
        return $this->date;
    }

    public function getMergedBy(): Administrator
    {
        return $this->mergedBy;
    }
}
