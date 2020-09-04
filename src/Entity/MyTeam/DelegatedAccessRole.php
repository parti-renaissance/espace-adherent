<?php

namespace App\Entity\MyTeam;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use ApiPlatform\Core\Annotation\ApiProperty;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="my_team_delegated_access_role")
 * @Algolia\Index(autoIndex=false)
 */
class DelegatedAccessRole
{
    /**
     * The unique auto incremented primary key.
     *
     * @var int|null
     *
     * @ORM\Id
     * @ORM\Column(type="integer", options={"unsigned": true})
     * @ORM\GeneratedValue
     *
     * @ApiProperty(identifier=false)
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     *
     * @Assert\Choice(DelegatedAccessEnum::TYPES)
     */
    private $type;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     *
     * @Assert\NotBlank(message="Veuillez renseigner un rôle.")
     * @Assert\Length(max=50)
     */
    private $name;

    /**
     * @var DelegatedAccessGroup
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\MyTeam\DelegatedAccessGroup", cascade={"persist"})
     * @ORM\JoinColumn(onDelete="CASCADE", nullable=true)
     */
    private $group;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getGroup(): ?DelegatedAccessGroup
    {
        return $this->group;
    }

    public function setGroup(?DelegatedAccessGroup $group): void
    {
        $this->group = $group;
    }

    public function __toString()
    {
        return $this->name;
    }
}
