<?php

declare(strict_types=1);

namespace App\Entity\ElectedRepresentative;

use App\Entity\EntityIdentityTrait;
use App\Entity\EntityTimestampableTrait;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

#[ORM\Entity]
#[ORM\Table(name: 'elected_representative_revenue_declaration')]
class RevenueDeclaration
{
    use EntityIdentityTrait;
    use EntityTimestampableTrait;

    #[ORM\Column(type: 'integer')]
    public int $amount;

    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: ElectedRepresentative::class, inversedBy: 'revenueDeclarations')]
    public ElectedRepresentative $electedRepresentative;

    public function __construct(?UuidInterface $uuid = null)
    {
        $this->uuid = $uuid ?? Uuid::uuid4();
    }

    public static function create(ElectedRepresentative $electedRepresentative, int $amount): self
    {
        $revenueDeclaration = new self();

        $revenueDeclaration->electedRepresentative = $electedRepresentative;
        $revenueDeclaration->amount = $amount;

        return $revenueDeclaration;
    }
}
