<?php

namespace App\Entity\AdherentMessage\Filter;

use ApiPlatform\Core\Annotation\ApiResource;
use App\AdherentMessage\Filter\AdherentMessageFilterInterface;
use App\Entity\AdherentMessage\AdherentMessageInterface;
use App\Entity\AdherentMessage\Segment\AudienceSegment;
use App\Validator\ValidMessageFilterSegment;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity
 *
 * @ApiResource(
 *     itemOperations={},
 *     collectionOperations={},
 *     subresourceOperations={},
 * )
 *
 * @ValidMessageFilterSegment
 */
abstract class AbstractAdherentMessageFilter extends AbstractAdherentFilter implements AdherentMessageFilterInterface
{
    /**
     * @var AdherentMessageInterface
     *
     * @ORM\OneToOne(targetEntity="App\Entity\AdherentMessage\AbstractAdherentMessage", mappedBy="filter")
     */
    private $message;

    /**
     * @var AudienceSegment|null
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\AdherentMessage\Segment\AudienceSegment")
     * @ORM\JoinColumn(onDelete="CASCADE")
     *
     * @Groups({"adherent_message_update_filter"})
     */
    private $segment;

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

    public function getSegment(): ?AudienceSegment
    {
        return $this->segment;
    }

    public function setSegment(?AudienceSegment $segment): void
    {
        $this->segment = $segment;
    }
}
