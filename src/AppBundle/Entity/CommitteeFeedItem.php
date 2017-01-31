<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;

/**
 * @ORM\Entity
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="type", type="string")
 * @ORM\DiscriminatorMap({"message"="CommitteeFeedMessage"})
 */
abstract class CommitteeFeedItem
{
    use EntityIdentityTrait;

    const MESSAGE = 'message';
    const EVENT = 'event';

    /**
     * @ORM\ManyToOne(targetEntity="Committee")
     */
    private $committee;

    /**
     * @var Adherent Any host of the committee
     *
     * @ORM\ManyToOne(targetEntity="Adherent")
     */
    private $author;

    /**
     * @var \DateTimeImmutable|\DateTime|null
     *
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    final public function __construct(Committee $committee, Adherent $adherent)
    {
        $this->uuid = Uuid::uuid5(Uuid::NAMESPACE_OID, 'committee_event');
        $this->committee = $committee;
        $this->author = $adherent;
        $this->createdAt = new \DateTimeImmutable();
    }
}
