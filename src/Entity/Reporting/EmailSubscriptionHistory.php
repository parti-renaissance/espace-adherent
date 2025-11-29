<?php

namespace App\Entity\Reporting;

use App\Entity\Adherent;
use App\Entity\SubscriptionType;
use App\Repository\EmailSubscriptionHistoryRepository;
use Cake\Chronos\Chronos;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\UuidInterface;

#[ORM\Entity(repositoryClass: EmailSubscriptionHistoryRepository::class)]
#[ORM\Index(columns: ['adherent_uuid'], name: 'adherent_email_subscription_histories_adherent_uuid_idx')]
#[ORM\Index(columns: ['action'], name: 'adherent_email_subscription_histories_adherent_action_idx')]
#[ORM\Index(columns: ['date'], name: 'adherent_email_subscription_histories_adherent_date_idx')]
#[ORM\Table(name: 'adherent_email_subscription_histories')]
class EmailSubscriptionHistory
{
    /**
     * @var int|null
     */
    #[ORM\Column(type: 'integer', options: ['unsigned' => true])]
    #[ORM\GeneratedValue]
    #[ORM\Id]
    private $id;

    /**
     * Only UUID is used instead of the Entity because we cannot lose this data if one unsubscribes.
     * Otherwise stats would be broken.
     *
     * @var UuidInterface
     */
    #[ORM\Column(type: 'uuid')]
    private $adherentUuid;

    /**
     * @var SubscriptionType
     */
    #[ORM\JoinColumn(nullable: false)]
    #[ORM\ManyToOne(targetEntity: SubscriptionType::class)]
    private $subscriptionType;

    /**
     * @var string
     */
    #[ORM\Column(length: 32)]
    private $action;

    /**
     * @var \DateTime
     */
    #[ORM\Column(type: 'datetime_immutable')]
    private $date;

    public function __construct(
        Adherent $adherent,
        SubscriptionType $subscriptionType,
        EmailSubscriptionHistoryAction $action,
        ?\DateTimeImmutable $date = null,
    ) {
        $this->adherentUuid = $adherent->getUuid();
        $this->subscriptionType = $subscriptionType;
        $this->action = $action->getValue();
        $this->date = $date ?: new Chronos();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAdherentUuid(): UuidInterface
    {
        return $this->adherentUuid;
    }

    public function getSubscriptionType(): string
    {
        return (string) $this->subscriptionType;
    }

    public function getDate(): \DateTimeImmutable
    {
        return $this->date;
    }

    public function getAction(): string
    {
        return $this->action;
    }
}
