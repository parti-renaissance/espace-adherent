<?php

namespace App\Entity\AdherentMessage\Filter;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use App\Entity\TerritorialCouncil\TerritorialCouncil;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 *
 * @Algolia\Index(autoIndex=false)
 */
class ReferentTerritorialCouncilFilter extends AbstractAdherentMessageFilter
{
    /**
     * @var TerritorialCouncil|null
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\TerritorialCouncil\TerritorialCouncil")
     *
     * @Assert\NotNull
     */
    private $territorialCouncil;

    public function getTerritorialCouncil(): ?TerritorialCouncil
    {
        return $this->territorialCouncil;
    }

    public function setTerritorialCouncil(?TerritorialCouncil $territorialCouncil): void
    {
        $this->territorialCouncil = $territorialCouncil;
    }
}
