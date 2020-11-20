<?php

namespace App\Entity\TerritorialCouncil;

use App\Entity\AbstractFeedItem;
use App\Entity\Adherent;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\TerritorialCouncil\TerritorialCouncilFeedItemRepository")
 */
class TerritorialCouncilFeedItem extends AbstractFeedItem
{
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\TerritorialCouncil\TerritorialCouncil")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $territorialCouncil;

    public function __construct(
        TerritorialCouncil $territorialCouncil,
        Adherent $author,
        string $content,
        string $createdAt = 'now'
    ) {
        parent::__construct($author, $content, $createdAt);

        $this->territorialCouncil = $territorialCouncil;
    }

    public function getTerritorialCouncil(): ?TerritorialCouncil
    {
        return $this->territorialCouncil;
    }
}
