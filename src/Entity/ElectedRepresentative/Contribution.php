<?php

namespace App\Entity\ElectedRepresentative;

use App\ElectedRepresentative\Contribution\ContributionTypeEnum;
use App\Entity\EntityIdentityTrait;
use App\Entity\EntityTimestampableTrait;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ElectedRepresentative\ContributionRepository")
 * @ORM\Table(name="elected_representative_contribution")
 */
class Contribution
{
    use EntityIdentityTrait;
    use EntityTimestampableTrait;

    /**
     * @ORM\Column(length=50)
     */
    public ?string $gocardlessCustomerId = null;

    /**
     * @ORM\Column(length=20)
     */
    public ?string $type = null;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\ElectedRepresentative\ElectedRepresentative", inversedBy="contributions")
     */
    public ?ElectedRepresentative $electedRepresentative = null;

    public function __construct(UuidInterface $uuid = null)
    {
        $this->uuid = $uuid ?? Uuid::uuid4();
    }

    public static function createMandate(
        ElectedRepresentative $electedRepresentative,
        string $gocardlessCustomerId
    ): self {
        $contribution = new self();

        $contribution->electedRepresentative = $electedRepresentative;
        $contribution->gocardlessCustomerId = $gocardlessCustomerId;
        $contribution->type = ContributionTypeEnum::MANDATE;

        return $contribution;
    }
}
