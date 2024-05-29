<?php

namespace App\Entity\Instance\NationalCouncil;

use App\Entity\VotingPlatform\Designation\BaseCandidaciesGroup;
use App\Entity\VotingPlatform\Designation\CandidacyInterface;
use App\Repository\Instance\NationalCouncil\CandidaciesGroupRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'national_council_candidacies_group')]
#[ORM\Entity(repositoryClass: CandidaciesGroupRepository::class)]
class CandidaciesGroup extends BaseCandidaciesGroup
{
    /**
     * @var CandidacyInterface[]|Collection
     */
    #[ORM\OneToMany(mappedBy: 'candidaciesGroup', targetEntity: Candidacy::class)]
    protected $candidacies;

    /**
     * @var string|null
     */
    #[ORM\Column(nullable: true)]
    private $label;

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(?string $label): void
    {
        $this->label = $label;
    }
}
