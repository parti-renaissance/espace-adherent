<?php

namespace AppBundle\Entity\ChezVous;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ChezVous\DepartmentRepository")
 * @ORM\Table(name="chez_vous_departments")
 *
 * @UniqueEntity("code")
 *
 * @Algolia\Index(autoIndex=false)
 */
class Department
{
    /**
     * @var int|null
     *
     * @ORM\Id
     * @ORM\Column(type="integer", options={"unsigned": true})
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string|null
     *
     * @ORM\Column(length=100)
     *
     * @Assert\NotBlank
     * @Assert\Length(max="100")
     *
     * @Algolia\Attribute
     */
    private $name;

    /**
     * @var string|null
     *
     * @ORM\Column(length=10, unique=true)
     *
     * @Assert\NotBlank
     * @Assert\Length(max="10")
     *
     * @Algolia\Attribute
     */
    private $code;

    /**
     * @var Region|null
     *
     * @ORM\ManyToOne(targetEntity=Region::class, inversedBy="departments", fetch="EAGER")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     *
     * @Algolia\Attribute
     */
    private $region;

    /**
     * @var City[]|Collection
     *
     * @ORM\OneToMany(targetEntity=City::class, mappedBy="department", cascade={"all"}, orphanRemoval=true, fetch="EXTRA_LAZY")
     */
    private $cities;

    public function __construct(Region $region = null, string $name = null, string $code = null)
    {
        $this->name = $name;
        $this->code = $code;
        $this->region = $region;
        $this->cities = new ArrayCollection();
    }

    public function __toString()
    {
        return sprintf('%s (%s)', $this->name, $this->code);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(?string $code): void
    {
        $this->code = $code;
    }

    public function getRegion(): ?Region
    {
        return $this->region;
    }

    public function setRegion(?Region $region): void
    {
        $this->region = $region;
    }

    public function getCities(): Collection
    {
        return $this->cities;
    }

    public function addCity(City $city): void
    {
        if (!$this->cities->contains($city)) {
            $city->setDepartment($this);
            $this->cities->add($city);
        }
    }

    public function removeCity(City $city): void
    {
        $this->cities->removeElement($city);
    }
}
