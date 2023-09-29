<?php

namespace App\Entity\VotingPlatform\Designation\CandidacyPool;

use App\Entity\EntityAdministratorBlameableInterface;
use App\Entity\EntityAdministratorBlameableTrait;
use App\Entity\EntityTimestampableTrait;
use App\Entity\VotingPlatform\Designation\BaseCandidaciesGroup;
use App\Entity\VotingPlatform\Designation\CandidacyInterface;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table("designation_candidacy_pool_candidacies_group")
 */
class CandidaciesGroup extends BaseCandidaciesGroup implements EntityAdministratorBlameableInterface
{
    use EntityTimestampableTrait;
    use EntityAdministratorBlameableTrait;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\VotingPlatform\Designation\CandidacyPool\CandidacyPool", inversedBy="candidaciesGroups")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     *
     * @Assert\NotBlank
     */
    public ?CandidacyPool $candidacyPool = null;

    /**
     * @var CandidacyInterface[]|Collection
     *
     * @ORM\OneToMany(targetEntity="App\Entity\VotingPlatform\Designation\CandidacyPool\Candidacy", mappedBy="candidaciesGroup", cascade={"persist"}, orphanRemoval=true)
     * @ORM\OrderBy({"position": "ASC"})
     *
     * @Assert\Valid
     * @Assert\Count(min=1)
     */
    protected $candidacies;

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
