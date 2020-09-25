<?php

namespace App\Entity\TerritorialCouncil;

use App\Entity\AbstractFeedItem;
use App\Entity\Adherent;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\TerritorialCouncil\PoliticalCommitteeFeedItemRepository")
 */
class PoliticalCommitteeFeedItem extends AbstractFeedItem
{
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\TerritorialCouncil\PoliticalCommittee")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
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
