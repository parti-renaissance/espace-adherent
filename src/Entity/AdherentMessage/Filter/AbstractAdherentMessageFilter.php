<?php

namespace App\Entity\AdherentMessage\Filter;

use ApiPlatform\Core\Annotation\ApiResource;
use App\AdherentMessage\Filter\AdherentMessageFilterInterface;
use App\Collection\ZoneCollection;
use App\Entity\AdherentMessage\AdherentMessageInterface;
use App\Entity\AdherentMessage\Segment\AudienceSegment;
use App\Entity\EntityZoneTrait;
use App\Validator\ValidMessageFilterSegment;
use Doctrine\Common\Collections\Collection;
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
    use EntityZoneTrait;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Geo\Zone", cascade={"persist"})
     * @ORM\JoinTable(
     *     name="adherent_message_filter_zone",
     *     joinColumns={
     *         @ORM\JoinColumn(name="adherent_message_filter_id", referencedColumnName="id", onDelete="CASCADE")
     *     },
     * )
     */
    protected Collection $zones;

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
