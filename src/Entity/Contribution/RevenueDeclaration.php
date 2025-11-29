<?php

declare(strict_types=1);

namespace App\Entity\Contribution;

use App\Entity\Adherent;
use App\Entity\EntityIdentityTrait;
use App\Entity\EntityTimestampableTrait;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity]
#[ORM\Table(name: 'contribution_revenue_declaration')]
class RevenueDeclaration
{
    use EntityIdentityTrait;
    use EntityTimestampableTrait;

    #[Groups(['adherent_elect_read'])]
    #[ORM\Column(type: 'integer')]
    public int $amount;

    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Adherent::class, inversedBy: 'revenueDeclarations')]
    public ?Adherent $adherent = null;

    public function __construct(?UuidInterface $uuid = null)
    {
        $this->uuid = $uuid ?? Uuid::uuid4();
    }

    public static function create(Adherent $adherent, int $amount): self
    {
        $revenueDeclaration = new self();

        $revenueDeclaration->adherent = $adherent;
        $revenueDeclaration->amount = $amount;

        return $revenueDeclaration;
    }
}
