<?php

namespace App\Entity\TerritorialCouncil;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use App\TerritorialCouncil\Exception\TerritorialCouncilQualityException;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table
 * @ORM\Entity
 *
 * @UniqueEntity(fields={"politicalCommitteeMembership", "name"})
 *
 * @Algolia\Index(autoIndex=false)
 */
class PoliticalCommitteeQuality
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer", options={"unsigned": true})
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var PoliticalCommitteeMembership|null
     *
     * @ORM\ManyToOne(targetEntity="PoliticalCommitteeMembership", inversedBy="qualities")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $politicalCommitteeMembership;

    /**
     * @ORM\Column(length=255)
     *
     * @Assert\NotBlank
     * @Assert\Length(max=255)
     */
    private $name;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="date")
     *
     * @Assert\NotNull
     */
    private $joinedAt;

    public function __construct(string $name, \DateTime $joinedAt = null)
    {
        $this->setName($name);
        $this->joinedAt = $joinedAt ?? new \DateTime('now');
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPoliticalCommitteeMembership(): PoliticalCommitteeMembership
    {
        return $this->politicalCommitteeMembership;
    }

    public function setPoliticalCommitteeMembership(PoliticalCommitteeMembership $politicalCommitteeMembership): void
    {
        $this->politicalCommitteeMembership = $politicalCommitteeMembership;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        self::checkQuality($name);
        $this->name = $name;
    }

    public function getJoinedAt(): \DateTime
    {
        return $this->joinedAt;
    }

    public function __toString(): string
    {
        return $this->name;
    }

    private static function checkQuality(string $quality): void
    {
        if (!TerritorialCouncilQualityEnum::isValid($quality)) {
            throw new TerritorialCouncilQualityException(sprintf('Invalid quality for PoliticalCommittee: "%s" given', $quality));
        }
    }
}
