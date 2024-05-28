<?php

namespace App\Entity\TerritorialCouncil;

use App\Entity\AbstractFeedItem;
use App\Entity\Adherent;
use App\Repository\TerritorialCouncil\PoliticalCommitteeFeedItemRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PoliticalCommitteeFeedItemRepository::class)]
class PoliticalCommitteeFeedItem extends AbstractFeedItem
{
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: PoliticalCommittee::class)]
    private $politicalCommittee;

    public function __construct(
        PoliticalCommittee $politicalCommittee,
        Adherent $author,
        string $content,
        string $createdAt = 'now'
    ) {
        parent::__construct($author, $content, $createdAt);

        $this->politicalCommittee = $politicalCommittee;
    }

    public function getPoliticalCommittee(): PoliticalCommittee
    {
        return $this->politicalCommittee;
    }
}
