<?php

namespace AppBundle\Entity\VotingPlatform;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use AppBundle\Entity\Committee;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 *
 * @ORM\Table(name="voting_platform_election_entity")
 *
 * @Algolia\Index(autoIndex=false)
 */
class ElectionEntity
{
    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var Committee|null
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Committee", cascade={"all"})
     * @ORM\JoinColumn(onDelete="SET NULL")
     */
    private $committee;

    public function getCommittee(): ?Committee
    {
        return $this->committee;
    }

    public function setCommittee(?Committee $committee): void
    {
        $this->committee = $committee;
    }
}
