<?php

namespace App\Entity\TerritorialCouncil;

use App\Entity\AbstractFeedItem;
use App\Entity\Adherent;
use App\Repository\TerritorialCouncil\TerritorialCouncilFeedItemRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TerritorialCouncilFeedItemRepository::class)]
class TerritorialCouncilFeedItem extends AbstractFeedItem
{
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: TerritorialCouncil::class)]
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
