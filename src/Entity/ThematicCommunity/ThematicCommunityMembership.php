<?php

namespace App\Entity\ThematicCommunity;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use App\Entity\EntityIdentityTrait;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;

/**
 * @ORM\Entity
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="type", type="string")
 * @ORM\DiscriminatorMap({
 *     "contact": "App\Entity\ThematicCommunity\ContactMembership",
 *     "adherent": "App\Entity\ThematicCommunity\AdherentMembership",
 *     "elected_representative": "App\Entity\ThematicCommunity\ElectedRepresentativeMembership",
 * })
 *
 * @Algolia\Index(autoIndex=false)
 */
abstract class ThematicCommunityMembership
{
    public const MOTIVATION_PASSIVE = 'passive';
    public const MOTIVATION_IDEA = 'idea';
    public const MOTIVATION_MOBILISATION = 'mobilisation';

    use EntityIdentityTrait;

    /**
     * @var ThematicCommunity
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\ThematicCommunity\ThematicCommunity")
     */
    protected $community;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     */
    private $joinedAt;

    /**
     * @var array
     *
     * @ORM\Column(type="simple_array", nullable=true)
     */
    private $categories = [];

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", options={"default": false})
     */
    private $association = false;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $associationName;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     */
    private $motivation;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", options={"default": false})
     */
    private $expert = false;

    public function __construct()
    {
        $this->uuid = Uuid::uuid4();
        $this->joinedAt = new \DateTime();
    }

    public function getJoinedAt(): \DateTime
    {
        return $this->joinedAt;
    }

    public function setJoinedAt(\DateTime $joinedAt): void
    {
        $this->joinedAt = $joinedAt;
    }

    public function getCommunity(): ThematicCommunity
    {
        return $this->community;
    }

    public function setCommuniy(ThematicCommunity $community): void
    {
        $this->community = $community;
    }

    public function getCategories(): array
    {
        return $this->categories;
    }

    public function setCategories(array $categories): void
    {
        $this->categories = $categories;
    }

    public function isAssociation(): bool
    {
        return $this->association;
    }

    public function setAssociation(bool $association): void
    {
        $this->association = $association;
    }

    public function getAssociationName(): string
    {
        return $this->associationName;
    }

    public function setAssociationName(string $associationName): void
    {
        $this->associationName = $associationName;
    }

    public function getMotivation(): string
    {
        return $this->motivation;
    }

    public function setMotivation(string $motivation): void
    {
        $this->motivation = $motivation;
    }

    public function isExpert(): bool
    {
        return $this->expert;
    }

    public function setExpert(bool $expert): void
    {
        $this->expert = $expert;
    }
}
