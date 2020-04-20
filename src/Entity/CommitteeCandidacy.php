<?php

namespace AppBundle\Entity;

use AppBundle\ValueObject\Genders;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\CommitteeCandidacyRepository")
 */
class CommitteeCandidacy
{
    use TimestampableEntity;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column
     */
    private $gender;

    /**
     * @var CommitteeElection
     *
     * @ORM\ManyToOne(targetEntity="CommitteeElection")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $committeeElection;

    public function __construct(CommitteeElection $election, string $gender)
    {
        $this->committeeElection = $election;
        $this->gender = $gender;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCommitteeElection(): CommitteeElection
    {
        return $this->committeeElection;
    }

    public function setCommitteeElection(CommitteeElection $committeeElection): void
    {
        $this->committeeElection = $committeeElection;
    }

    public function getGender(): string
    {
        return $this->gender;
    }

    public function setGender(string $gender): void
    {
        $this->gender = $gender;
    }

    public function getCivility(): string
    {
        return $this->isMale() ? 'M.' : 'Mme.';
    }

    public function isMale(): bool
    {
        return Genders::MALE === $this->gender;
    }

    public function isFemale(): bool
    {
        return Genders::FEMALE === $this->gender;
    }
}
