<?php

namespace App\Entity\TerritorialCouncil;

use App\TerritorialCouncil\Exception\TerritorialCouncilQualityException;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @UniqueEntity(fields={"politicalCommitteeMembership", "name"})
 */
#[ORM\Entity]
class PoliticalCommitteeQuality
{
    #[ORM\Id]
    #[ORM\Column(type: 'integer', options: ['unsigned' => true])]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private $id;

    /**
     * @var PoliticalCommitteeMembership|null
     */
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: PoliticalCommitteeMembership::class, inversedBy: 'qualities')]
    private $politicalCommitteeMembership;

    /**
     * @Assert\NotBlank
     * @Assert\Length(max=255)
     */
    #[ORM\Column]
    private $name;

    /**
     * @var \DateTime
     *
     * @Assert\NotNull
     */
    #[ORM\Column(type: 'datetime')]
    private $joinedAt;

    public function __construct(string $name, ?\DateTime $joinedAt = null)
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
