<?php

namespace App\Entity\Instance\NationalCouncil;

use App\Entity\VotingPlatform\Designation\BaseCandidaciesGroup;
use App\Entity\VotingPlatform\Designation\CandidacyInterface;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Instance\NationalCouncil\CandidaciesGroupRepository")
 * @ORM\Table(name="national_council_candidacies_group")
 */
class CandidaciesGroup extends BaseCandidaciesGroup
{
    /**
     * @var CandidacyInterface[]|Collection
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Instance\NationalCouncil\Candidacy", mappedBy="candidaciesGroup")
     */
    protected $candidacies;

    /**
     * @var string|null
     *
     * @ORM\Column(nullable=true)
     */
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
