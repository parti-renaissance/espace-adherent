<?php

namespace AppBundle\Entity;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 *
 * @Algolia\Index(autoIndex=false)
 */
class AssessorRoleAssociation
{
    /**
     * @var int|null
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue
     */
    private $id;

    /**
     * @var VotePlace
     *
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\VotePlace")
     */
    private $votePlace;

    public function __construct(VotePlace $votePlace)
    {
        $this->votePlace = $votePlace;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getVotePlace(): VotePlace
    {
        return $this->votePlace;
    }
}
