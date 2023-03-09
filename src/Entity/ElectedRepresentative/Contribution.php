<?php

namespace App\Entity\ElectedRepresentative;

use App\ElectedRepresentative\Contribution\ContributionTypeEnum;
use App\Entity\EntityIdentityTrait;
use App\Entity\EntityTimestampableTrait;
use App\GoCardless\Subscription;
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
     * @ORM\Column(length=50)
     */
    public ?string $gocardlessBankAccountId = null;

    /**
     * @ORM\Column(type="boolean", options={"default": true})
     */
    public ?bool $gocardlessBankAccountEnabled = null;

    /**
     * @ORM\Column(length=50)
     */
    public ?string $gocardlessMandateId = null;

    /**
     * @ORM\Column(length=20)
     */
    public ?string $gocardlessMandateStatus = null;

    /**
     * @ORM\Column(length=50)
     */
    public ?string $gocardlessSubscriptionId = null;

    /**
     * @ORM\Column(length=20)
     */
    public ?string $gocardlessSubscriptionStatus = null;

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

    public static function fromSubscription(
        ElectedRepresentative $electedRepresentative,
        Subscription $subscription
    ): self {
        $contribution = new self();

        $contribution->electedRepresentative = $electedRepresentative;
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
