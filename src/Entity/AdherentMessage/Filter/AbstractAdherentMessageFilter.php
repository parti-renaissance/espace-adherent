<?php

namespace App\Entity\AdherentMessage\Filter;

use App\AdherentMessage\Filter\AdherentMessageFilterInterface;
use App\Entity\AdherentMessage\AdherentMessageInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
abstract class AbstractAdherentMessageFilter extends AbstractAdherentFilter implements AdherentMessageFilterInterface
{
    /**
     * @var AdherentMessageInterface
     *
     * @ORM\OneToOne(targetEntity="App\Entity\AdherentMessage\AbstractAdherentMessage", mappedBy="filter")
     */
    private $message;

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
