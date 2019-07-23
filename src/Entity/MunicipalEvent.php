<?php

namespace AppBundle\Entity;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use AppBundle\Event\EventInterface;
use AppBundle\Geocoder\GeoPointInterface;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity
 *
 * @ApiResource(
 *     attributes={
 *         "normalization_context": {"groups": {"event_read"}},
 *         "order": {"beginAt": "DESC"},
 *     },
 *     collectionOperations={"get"},
 *     itemOperations={"get"},
 * )
 *
 * @ApiFilter(SearchFilter::class, properties={"postAddress.postalCode": "exact"})
 *
 * @Algolia\Index(autoIndex=false)
 */
class MunicipalEvent implements EventInterface, GeoPointInterface, ReferentTaggableEntity
{
    use EntityIdentityTrait;
    use EntityPostAddressTrait;
    use EntityReferentTagTrait;
    use EntityTimestampableTrait;

    /**
     * @var Collection|ReferentTag[]
     *
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\ReferentTag")
     */
    protected $referentTags;

    /**
     * @var string|null
     *
     * @ORM\Column(length=100)
     *
     * @Groups({"event_read"})
     */
    protected $name;

    /**
     * The event canonical name.
     *
     * @var string|null
     *
     * @ORM\Column(length=100)
     */
    protected $canonicalName;

    /**
     * @var string|null
     *
     * @ORM\Column(length=130)
     * @Gedmo\Slug(
     *     fields={"beginAt", "canonicalName"},
     *     dateFormat="Y-m-d",
     *     handlers={@Gedmo\SlugHandler(class="AppBundle\Event\UniqueEventNameHandler")}
     * )
     *
     * @Groups({"event_read"})
     */
    protected $slug;

    /**
     * @var string
     *
     * @ORM\Column(type="text")
     *
     * @Groups({"event_read"})
     */
    protected $description;

    /**
     * @var string
     *
     * @ORM\Column(length=50)
     *
     * @Groups({"event_read"})
     */
    protected $timeZone;

    /**
     * @var \DateTimeInterface|null
     *
     * @ORM\Column(type="datetime")
     *
     * @Groups({"event_read"})
     */
    protected $beginAt;

    /**
     * @var \DateTimeInterface|null
     *
     * @ORM\Column(type="datetime")
     *
     * @Groups({"event_read"})
     */
    protected $finishAt;

    /**
     * @var Adherent|null
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Adherent")
     * @ORM\JoinColumn(onDelete="RESTRICT")
     */
    protected $organizer;

    /**
     * @var int
     *
     * @ORM\Column(type="smallint", options={"unsigned": true})
     *
     * @Groups({"event_read"})
     */
    protected $participantsCount;

    /**
     * @var string|null
     *
     * @ORM\Column(length=20)
     *
     * @Groups({"event_read"})
     */
    protected $status;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", options={"default": true})
     */
    protected $published = true;

    /**
     * @var int|null
     *
     * @ORM\Column(type="integer", nullable=true)
     *
     * @Groups({"event_read"})
     */
    protected $capacity;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\EventCategory")
     *
     * @Algolia\Attribute
     */
    protected $category;
}
