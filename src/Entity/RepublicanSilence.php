<?php

namespace App\Entity;

use App\Entity\Geo\Zone;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 */
class RepublicanSilence
{
    use EntityZoneTrait;

    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\Column(type="integer", options={"unsigned": true})
     * @ORM\GeneratedValue
     */
    private $id;

    /**
     * @var ReferentTag[]|Collection
     *
     * @ORM\ManyToMany(targetEntity="ReferentTag")
     *
     * @Assert\Count(min=1)
     *
     * @Groups({"read_api"})
     */
    private $referentTags;

    /**
     * @var Collection|Zone[]
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\Geo\Zone", cascade={"persist"})
     * @Groups({"read_api"})
     */
    protected $zones;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     *
     * @Assert\NotBlank
     * @Assert\DateTime
     *
     * @Groups({"read_api"})
     */
    private $beginAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     *
     * @Assert\NotBlank
     * @Assert\DateTime
     * @Assert\Expression("value > this.getBeginAt()", message="committee.event.invalid_date_range")
     *
     * @Groups({"read_api"})
     */
    private $finishAt;

    public function __construct()
    {
        $this->zones = new ArrayCollection();
    }

    /**
     * @return ReferentTag[]|Collection
     */
    public function getReferentTags(): Collection
    {
        return $this->referentTags;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getBeginAt(): ?\DateTime
    {
        return $this->beginAt;
    }

    public function setBeginAt(\DateTime $beginAt): void
    {
        $this->beginAt = $beginAt;
    }

    public function getFinishAt(): ?\DateTime
    {
        return $this->finishAt;
    }

    public function setFinishAt(\DateTime $finishAt): void
    {
        $this->finishAt = $finishAt;
    }
}
