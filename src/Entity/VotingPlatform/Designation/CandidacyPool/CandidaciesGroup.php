<?php

declare(strict_types=1);

namespace App\Entity\VotingPlatform\Designation\CandidacyPool;

use App\Entity\EntityAdministratorBlameableInterface;
use App\Entity\EntityAdministratorBlameableTrait;
use App\Entity\EntityTimestampableTrait;
use App\Entity\VotingPlatform\Designation\BaseCandidaciesGroup;
use App\Entity\VotingPlatform\Designation\CandidacyInterface;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\Table('designation_candidacy_pool_candidacies_group')]
class CandidaciesGroup extends BaseCandidaciesGroup implements EntityAdministratorBlameableInterface
{
    use EntityTimestampableTrait;
    use EntityAdministratorBlameableTrait;

    #[Assert\NotBlank]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: CandidacyPool::class, inversedBy: 'candidaciesGroups')]
    public ?CandidacyPool $candidacyPool = null;

    /**
     * @var CandidacyInterface[]|Collection
     */
    #[Assert\Count(min: 1)]
    #[Assert\Valid]
    #[ORM\OneToMany(mappedBy: 'candidaciesGroup', targetEntity: Candidacy::class, cascade: ['persist'], orphanRemoval: true)]
    #[ORM\OrderBy(['position' => 'ASC'])]
    protected $candidacies;

    #[ORM\Column(nullable: true)]
    public ?string $label = null;

    public function addCandidacy(CandidacyInterface $candidacy): void
    {
        if ($this->candidacies->isEmpty()) {
            $candidacy->setPosition(1);
        }

        $candidacy->candidacyPool = $this->candidacyPool;

        parent::addCandidacy($candidacy);
    }

    /**
     * @return Candidacy[]
     */
    public function getCandidacies(): array
    {
        return $this->candidacies->toArray();
    }

    /**
     * @return Candidacy[]
     */
    public function getCandidaciesByType(bool $substituteCandidates = false): array
    {
        return $this->candidacies->filter(
            function (Candidacy $candidacy) use ($substituteCandidates) {
                return $substituteCandidates === $candidacy->isSubstitute;
            })->toArray();
    }
}
