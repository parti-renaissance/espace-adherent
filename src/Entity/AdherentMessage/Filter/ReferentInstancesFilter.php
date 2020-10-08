<?php

namespace App\Entity\AdherentMessage\Filter;

use App\Entity\TerritorialCouncil\PoliticalCommittee;
use App\Entity\TerritorialCouncil\TerritorialCouncil;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 *
 * @Assert\Expression(
 *     "(this.getTerritorialCouncil() && !this.getPoliticalCommittee()) || (!this.getTerritorialCouncil() && this.getPoliticalCommittee())",
 *     message="Vous ne pouvez pas filtrer sur le conseil territorial et le comité politique en même temps."
 * )
 */
class ReferentInstancesFilter extends AbstractAdherentMessageFilter
{
    private const INSTANCE_TYPE_COTERR = 'territorial_council';
    private const INSTANCE_TYPE_COPOL = 'political_committee';

    /**
     * @var TerritorialCouncil|null
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\TerritorialCouncil\TerritorialCouncil")
     */
    private $territorialCouncil;

    /**
     * @var PoliticalCommittee|null
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\TerritorialCouncil\PoliticalCommittee")
     */
    private $politicalCommittee;

    /**
     * @var string[]
     *
     * @ORM\Column(type="simple_array")
     */
    private $qualities = [];

    public function getTerritorialCouncil(): ?TerritorialCouncil
    {
        return $this->territorialCouncil;
    }

    public function setTerritorialCouncil(?TerritorialCouncil $territorialCouncil): void
    {
        $this->territorialCouncil = $territorialCouncil;
    }

    public function getPoliticalCommittee(): ?PoliticalCommittee
    {
        return $this->politicalCommittee;
    }

    public function setPoliticalCommittee(?PoliticalCommittee $politicalCommittee): void
    {
        $this->politicalCommittee = $politicalCommittee;
    }

    public function getQualities(): array
    {
        return $this->qualities;
    }

    public function setQualities(array $qualities): void
    {
        $this->qualities = $qualities;
    }

    public function getInstanceType(): ?string
    {
        if ($this->territorialCouncil) {
            return self::INSTANCE_TYPE_COTERR;
        }

        if ($this->politicalCommittee) {
            return self::INSTANCE_TYPE_COPOL;
        }

        return null;
    }
}
