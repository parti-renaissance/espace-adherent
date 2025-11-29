<?php

declare(strict_types=1);

namespace App\Entity\Contribution;

use App\Contribution\ContributionTypeEnum;
use App\Entity\Adherent;
use App\Entity\EntityIdentityTrait;
use App\Entity\EntityTimestampableTrait;
use App\GoCardless\Subscription;
use App\Repository\Contribution\ContributionRepository;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Attribute\SerializedName;

#[ORM\Entity(repositoryClass: ContributionRepository::class)]
#[ORM\Table(name: 'contribution')]
class Contribution
{
    use EntityIdentityTrait;
    use EntityTimestampableTrait;

    #[ORM\Column(type: 'datetime', nullable: true)]
    public ?\DateTime $startDate = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    public ?\DateTime $endDate = null;

    #[ORM\Column(length: 50)]
    public ?string $gocardlessCustomerId = null;

    #[ORM\Column(length: 50)]
    public ?string $gocardlessBankAccountId = null;

    #[ORM\Column(type: 'boolean', options: ['default' => true])]
    public ?bool $gocardlessBankAccountEnabled = null;

    #[ORM\Column(length: 50)]
    public ?string $gocardlessMandateId = null;

    #[ORM\Column(length: 20)]
    #[SerializedName('status')]
    public ?string $gocardlessMandateStatus = null;

    #[ORM\Column(length: 50)]
    public ?string $gocardlessSubscriptionId = null;

    #[ORM\Column(length: 20)]
    public ?string $gocardlessSubscriptionStatus = null;

    #[ORM\Column(length: 20)]
    public ?string $type = null;

    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Adherent::class, inversedBy: 'contributions')]
    public ?Adherent $adherent = null;

    public function __construct(?UuidInterface $uuid = null)
    {
        $this->uuid = $uuid ?? Uuid::uuid4();
    }

    public static function fromSubscription(
        Adherent $adherent,
        Subscription $subscription,
    ): self {
        $contribution = new self();

        $contribution->adherent = $adherent;
        $contribution->gocardlessCustomerId = $subscription->customer->id;
        $contribution->gocardlessBankAccountId = $subscription->bankAccount->id;
        $contribution->gocardlessBankAccountEnabled = $subscription->bankAccount->enabled;
        $contribution->gocardlessMandateId = $subscription->mandate->id;
        $contribution->gocardlessMandateStatus = $subscription->mandate->status;
        $contribution->gocardlessSubscriptionId = $subscription->subscription->id;
        $contribution->gocardlessSubscriptionStatus = $subscription->subscription->status;
        $contribution->type = ContributionTypeEnum::MANDATE;

        return $contribution;
    }
}
