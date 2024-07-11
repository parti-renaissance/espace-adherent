<?php

namespace App\Entity\Election;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class ListTotalResult
{
    /**
     * @var int|null
     */
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue]
    #[ORM\Id]
    private $id;

    /**
     * @var VoteResultList|null
     */
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: VoteResultList::class)]
    private $list;

    /**
     * @var int|null
     */
    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $total = 0;

    /**
     * @var BaseWithListCollectionResult|null
     */
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: BaseWithListCollectionResult::class, inversedBy: 'listTotalResults')]
    private $voteResult;

    public function __construct(?VoteResultList $list = null)
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
