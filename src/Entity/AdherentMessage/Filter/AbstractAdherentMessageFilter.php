<?php

namespace App\Entity\AdherentMessage\Filter;

use ApiPlatform\Metadata\ApiResource;
use App\AdherentMessage\Filter\AdherentMessageFilterInterface;
use App\Collection\ZoneCollection;
use App\Entity\AdherentMessage\AdherentMessage;
use App\Entity\AdherentMessage\AdherentMessageInterface;
use App\Entity\AdherentMessage\Segment\AudienceSegment;
use App\Entity\EntityZoneTrait;
use App\Entity\Geo\Zone;
use App\Validator\ValidMessageFilterSegment;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ApiResource(operations: [])]
#[ORM\Entity]
#[ValidMessageFilterSegment]
abstract class AbstractAdherentMessageFilter extends AbstractAdherentFilter implements AdherentMessageFilterInterface
{
    use EntityZoneTrait;

    #[Groups(['adherent_message_read_filter'])]
    #[ORM\JoinColumn(name: 'adherent_message_filter_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\JoinTable(name: 'adherent_message_filter_zone')]
    #[ORM\ManyToMany(targetEntity: Zone::class, cascade: ['persist'])]
    protected Collection $zones;

    /**
     * @var AdherentMessageInterface
     */
    #[ORM\OneToOne(mappedBy: 'filter', targetEntity: AdherentMessage::class)]
    private $message;

    /**
     * @var AudienceSegment|null
     */
    #[Groups(['adherent_message_update_filter'])]
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: AudienceSegment::class)]
    private $segment;

    public function __construct(array $zones = [])
    {
        $this->zones = new ZoneCollection($zones);
    }

    public function getExternalId(): ?string
    {
        return $this->message->getExternalId();
    }

    public function getMessage(): ?AdherentMessageInterface
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

    public function reset(): void
    {
        $this->segment = null;

        parent::reset();
    }
}
