<?php

declare(strict_types=1);

namespace App\Entity\VotingPlatform\Designation\CandidacyPool;

use App\Entity\Adherent;
use App\Entity\VotingPlatform\Designation\BaseCandidaciesGroup;
use App\Entity\VotingPlatform\Designation\BaseCandidacy;
use App\Entity\VotingPlatform\Designation\ElectionEntityInterface;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Ramsey\Uuid\UuidInterface;
use Runroom\SortableBehaviorBundle\Behaviors\Sortable;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\Table('designation_candidacy_pool_candidacy')]
class Candidacy extends BaseCandidacy
{
    use Sortable;

    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: CandidacyPool::class)]
    public ?CandidacyPool $candidacyPool = null;

    /**
     * @var CandidaciesGroup|null
     */
    #[Gedmo\SortableGroup]
    #[ORM\ManyToOne(targetEntity: CandidaciesGroup::class, inversedBy: 'candidacies')]
    protected $candidaciesGroup;

    #[ORM\ManyToOne(targetEntity: Adherent::class)]
    private ?Adherent $adherent = null;

    #[Assert\NotBlank]
    #[ORM\Column]
    private ?string $firstName = null;

    #[Assert\NotBlank]
    #[ORM\Column]
    private ?string $lastName = null;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    public bool $isSubstitute = false;

    public function __construct(?CandidacyPool $election = null, ?string $gender = null, ?UuidInterface $uuid = null)
    {
        parent::__construct($gender, $uuid);

        $this->candidacyPool = $election;
    }

    public function getCandidacyPool(): ElectionEntityInterface
    {
        return $this->candidacyPool;
    }

    public function setCandidacyPool(CandidacyPool $candidacyPool): void
    {
        $this->candidacyPool = $candidacyPool;
    }

    protected function createCandidaciesGroup(): BaseCandidaciesGroup
    {
        return new CandidaciesGroup();
    }

    public function getType(): string
    {
        return $this->getCandidacyPool()->getDesignationType();
    }

    public function getAdherent(): ?Adherent
    {
        return $this->adherent;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(?string $firstName): void
    {
        $this->firstName = $firstName;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(?string $lastName): void
    {
        $this->lastName = $lastName;
    }

    public function getFullName(): string
    {
        return \sprintf('%s %s', $this->firstName, $this->lastName);
    }

    public function getElection(): ElectionEntityInterface
    {
        return $this->candidacyPool;
    }
}
