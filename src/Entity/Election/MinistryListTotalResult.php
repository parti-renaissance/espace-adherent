<?php

namespace App\Entity\Election;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class MinistryListTotalResult
{
    use ListFieldTrait;

    /**
     * @var int|null
     */
    #[ORM\Column(type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    private $id;

    /**
     * @var int|null
     */
    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $total = 0;

    /**
     * @var MinistryVoteResult|null
     */
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: MinistryVoteResult::class, inversedBy: 'listTotalResults')]
    private $ministryVoteResult;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTotal(): ?int
    {
        return $this->total;
    }

    public function setTotal(?int $total): void
    {
        $this->total = $total;
    }

    public function getMinistryVoteResult(): ?MinistryVoteResult
    {
        return $this->ministryVoteResult;
    }

    public function setMinistryVoteResult(MinistryVoteResult $ministryVoteResult): void
    {
        $this->ministryVoteResult = $ministryVoteResult;
    }
}
