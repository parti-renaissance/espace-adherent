<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="elections")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ElectionRepository")
 *
 * @UniqueEntity("name")
 */
class Election
{
    /**
     * @var int|null
     *
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(unique=true)
     *
     * @Assert\NotBlank
     * @Assert\Length(max=255)
     */
    private $name = '';

    /**
     * @var string
     *
     * @ORM\Column(type="text")
     *
     * @Assert\NotBlank
     */
    private $introduction = '';

    /**
     * @var string|null
     *
     * @ORM\Column(nullable=true)
     *
     * @Assert\Length(max=255)
     */
    private $place;

    /**
     * @var ElectionRound[]|Collection
     *
     * @ORM\OneToMany(targetEntity="ElectionRound", mappedBy="election", cascade={"all"}, orphanRemoval=true)
     *
     * @Assert\Count(min=1)
     */
    private $rounds;

    public function __construct()
    {
        $this->rounds = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getIntroduction(): string
    {
        return $this->introduction;
    }

    public function setIntroduction(string $introduction): void
    {
        $this->introduction = $introduction;
    }

    public function getPlace(): ?string
    {
        return $this->place;
    }

    public function setPlace(?string $place): void
    {
        $this->place = $place;
    }

    /**
     * @return ElectionRound[]|Collection
     */
    public function getRounds(): Collection
    {
        return $this->rounds;
    }

    public function addRound(ElectionRound $round): void
    {
        if (!$this->rounds->contains($round)) {
            $this->rounds->add($round);
            $round->setElection($this);
        }
    }

    public function removeRound($round): void
    {
        $this->rounds->removeElement($round);
    }
}
