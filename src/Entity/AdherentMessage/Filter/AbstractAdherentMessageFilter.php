<?php

namespace AppBundle\Entity\AdherentMessage\Filter;

use AppBundle\AdherentMessage\Filter\AdherentMessageFilterInterface;
use AppBundle\Entity\AdherentMessage\AdherentMessageInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="adherent_message_filters")
 * @ORM\InheritanceType("SINGLE_TABLE")
 */
abstract class AbstractAdherentMessageFilter implements AdherentMessageFilterInterface
{
    /**
     * @var int
     *
     * @ORM\Column(type="integer", options={"unsigned": true})
     * @ORM\Id
     * @ORM\GeneratedValue
     */
    private $id;

    /**
     * @var AdherentMessageInterface
     *
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\AdherentMessage\AbstractAdherentMessage", mappedBy="filter")
     */
    private $message;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", options={"default": false})
     */
    private $synchronized = false;

    public function setSynchronized(bool $value): void
    {
        $this->synchronized = $value;
    }

    public function isSynchronized(): bool
    {
        return $this->synchronized;
    }

    public function getExternalId(): ?string
    {
        return $this->message->getExternalId();
    }

    public function getMessage(): AdherentMessageInterface
    {
        return $this->message;
    }

    public function setMessage(AdherentMessageInterface $message): void
    {
        $this->message = $message;
    }
}
