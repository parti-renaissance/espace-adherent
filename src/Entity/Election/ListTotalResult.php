<?php

namespace AppBundle\Entity\Election;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 *
 * @Algolia\Index(autoIndex=false)
 */
class ListTotalResult
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
     * @var VoteResultList|null
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Election\VoteResultList")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $list;

    /**
     * @var int|null
     *
     * @ORM\Column(type="integer", options={"default": 0})
     */
    private $total = 0;

    /**
     * @var BaseWithListCollectionResult|null
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Election\BaseWithListCollectionResult", inversedBy="listTotalResults")
     * @ORM\JoinColumn(onDelete="CASCADE", nullable=false)
     */
    private $voteResult;

    public function __construct(VoteResultList $list = null)
    {
        $this->list = $list;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getList(): ?VoteResultList
    {
        return $this->list;
    }

    public function setList(?VoteResultList $list): void
    {
        $this->list = $list;
    }

    public function getTotal(): ?int
    {
        return $this->total;
    }

    public function setTotal(?int $total): void
    {
        $this->total = $total;
    }

    public function getVoteResult(): ?BaseWithListCollectionResult
    {
        return $this->voteResult;
    }

    public function setVoteResult(BaseWithListCollectionResult $voteResult): void
    {
        $this->voteResult = $voteResult;
    }
}
