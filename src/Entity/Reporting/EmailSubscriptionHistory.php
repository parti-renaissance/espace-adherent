<?php

namespace AppBundle\Entity\Reporting;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\ReferentTag;
use Cake\Chronos\Chronos;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * @ORM\Table(
 *      name="adherent_email_subscription_histories",
 *      indexes={
 *          @ORM\Index(name="adherent_email_subscription_histories_adherent_uuid_idx", columns="adherent_uuid"),
 *          @ORM\Index(name="adherent_email_subscription_histories_adherent_action_idx", columns="action"),
 *          @ORM\Index(name="adherent_email_subscription_histories_adherent_date_idx", columns="date"),
 *          @ORM\Index(name="adherent_email_subscription_histories_adherent_email_type_idx", columns="subscribed_email_type"),
 *      }
 * )
 * @ORM\Entity(repositoryClass="AppBundle\Repository\EmailSubscriptionHistoryRepository")
 *
 * @Algolia\Index(autoIndex=false)
 */
class EmailSubscriptionHistory
{
    /**
     * @var UuidInterface
     *
     * @ORM\Column(type="uuid")
     * @ORM\Id
     */
    private $uuid;

    /**
     * Only UUID is used instead of the Entity because we cannot lose this data if one unsubscribes.
     * Otherwise stats would be broken.
     *
     * @var UuidInterface
     *
     * @ORM\Column(type="uuid")
     */
    private $adherentUuid;

    /**
     * @var string
     *
     * @ORM\Column(length=50)
     */
    private $subscribedEmailType;

    /**
     * @var ReferentTag
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\ReferentTag")
     */
    private $referentTag;

    /**
     * @var string
     *
     * @ORM\Column(length=32)
     */
    private $action;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime_immutable")
     */
    private $date;

    public function __construct(
        Adherent $adherent,
        string $subscribedEmailType,
        ReferentTag $referentTag,
        EmailSubscriptionHistoryAction $action,
        \DateTimeImmutable $date = null
    ) {
        $this->uuid = Uuid::uuid4();
        $this->adherentUuid = $adherent->getUuid();
        $this->subscribedEmailType = $subscribedEmailType;
        $this->referentTag = $referentTag;
        $this->action = $action->getValue();
        $this->date = $date ?: new Chronos();
    }

    public function getUuid(): UuidInterface
    {
        return $this->uuid;
    }

    public function getAdherentUuid(): UuidInterface
    {
        return $this->adherentUuid;
    }

    public function getSubscribedEmailType(): string
    {
        return $this->subscribedEmailType;
    }

    public function getReferentTag(): ReferentTag
    {
        return $this->referentTag;
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
