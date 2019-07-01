<?php

namespace AppBundle\Entity\Oldolf;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use AppBundle\Validator\OldolfMeasurePayload;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\Oldolf\MeasureRepository")
 * @ORM\Table(name="oldolf_measures", uniqueConstraints={
 *     @ORM\UniqueConstraint(name="oldolf_measures_city_type_unique", columns={"city_id", "type"}),
 * })
 *
 * @Algolia\Index(autoIndex=false)
 *
 * @OldolfMeasurePayload
 *
 * @UniqueEntity(fields={"city", "type"}, errorPath="type")
 */
class Measure
{
    /**
     * @var int|null
     *
     * @ORM\Id
     * @ORM\Column(type="integer", options={"unsigned": true})
     * @ORM\GeneratedValue
     */
    private $id;

    /**
     * @var string|null
     *
     * @ORM\Column
     *
     * @Assert\NotBlank
     * @Assert\Length(max="255")
     * @Assert\Choice(callback={"AppBundle\Oldolf\MeasureChoiceLoader", "getTypeChoices"})
     *
     * @Algolia\Attribute
     */
    private $type;

    /**
     * @var array
     *
     * @ORM\Column(type="json_array", nullable=true)
     *
     * @Algolia\Attribute
     */
    private $payload;

    /**
     * @var City|null
     *
     * @ORM\ManyToOne(targetEntity=City::class, inversedBy="measures")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     *
     * @Assert\NotBlank
     */
    private $city;

    public function __construct(City $city = null, string $type = null, array $payload = null)
    {
        $this->type = $type;
        $this->payload = $payload;
        $this->city = $city;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): void
    {
        $this->type = $type;
    }

    public function getPayload(): ?array
    {
        return $this->payload;
    }

    public function setPayload(?array $payload): void
    {
        $this->payload = $payload;
    }

    public function getCity(): ?City
    {
        return $this->city;
    }

    public function setCity(?City $city): void
    {
        $this->city = $city;
    }

    public function getEntries(): iterable
    {
        if (!$this->payload) {
            return [];
        }

        $entries = [];

        foreach ($this->payload as $key => $value) {
            $entries[] = [
                'key' => $key,
                'value' => $value,
            ];
        }

        return $entries;
    }

    public function setEntries(iterable $entries): void
    {
        if (empty($entries)) {
            $this->payload = null;
        }

        $this->payload = [];

        foreach ($entries as $entry) {
            if (!array_key_exists('key', $entry) || !array_key_exists('value', $entry)) {
                continue;
            }

            $this->payload[$entry['key']] = $entry['value'];
        }
    }
}
