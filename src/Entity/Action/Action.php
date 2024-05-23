<?php

namespace App\Entity\Action;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Collection\ZoneCollection;
use App\Entity\AuthoredTrait;
use App\Entity\AuthorInterface;
use App\Entity\EntityIdentityTrait;
use App\Entity\EntityPostAddressTrait;
use App\Entity\EntityTimestampableTrait;
use App\Entity\EntityZoneTrait;
use App\Entity\Geo\Zone;
use App\Entity\ZoneableEntity;
use App\Geocoder\GeoPointInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="vox_action")
 *
 * @ApiResource(
 *     attributes={
 *         "denormalization_context": {"groups": {"action_write"}},
 *         "normalization_context": {"groups": {"action_read"}},
 *     },
 *     itemOperations={
 *         "put": {
 *             "path": "/v3/actions/{uuid}",
 *             "security": "is_granted('IS_FEATURE_GRANTED', 'actions') and (object.getAuthor() == user or user.hasDelegatedFromUser(object.getAuthor(), 'actions'))",
 *         }
 *     },
 *     collectionOperations={
 *         "post": {
 *             "path": "/v3/actions",
 *             "security": "is_granted('IS_FEATURE_GRANTED', 'actions')",
 *         },
 *     }
 * )
 */
class Action implements AuthorInterface, GeoPointInterface, ZoneableEntity
{
    use EntityIdentityTrait;
    use EntityPostAddressTrait;
    use EntityZoneTrait;
    use EntityTimestampableTrait;
    use AuthoredTrait;

    /**
     * @ORM\Column(name="`type`")
     *
     * @Assert\NotBlank
     */
    #[Groups(['action_read', 'action_write'])]
    public ?string $type = null;

    /**
     * @ORM\Column(type="datetime")
     *
     * @Assert\NotBlank
     */
    #[Groups(['action_read', 'action_write'])]
    public ?\DateTime $date = null;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    #[Groups(['action_read', 'action_write'])]
    public ?string $description = null;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Action\ActionParticipant", mappedBy="action", fetch="EAGER")
     */
    private Collection $participants;

    /**
     * @var ZoneCollection|Zone[]
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\Geo\Zone")
     * @ORM\JoinTable(name="vox_action_zone")
     *
     * @Groups({"action_read"})
     */
    protected Collection $zones;

    public function __construct()
    {
        $this->uuid = Uuid::uuid4();
        $this->participants = new ArrayCollection();
        $this->zones = new ArrayCollection();
    }
}
