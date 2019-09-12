<?php

namespace AppBundle\Entity;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use AppBundle\Intl\FranceCitiesBundle;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="municipal_chief_areas")
 * @ORM\Entity
 *
 * @Algolia\Index(autoIndex=false)
 */
class MunicipalChiefManagedArea
{
    /**
     * @var int|null
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column
     */
    private $inseeCode;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", options={"default": false})
     */
    private $jecouteAccess = false;

    public function hasJecouteAccess(): bool
    {
        return $this->jecouteAccess;
    }

    public function setJecouteAccess(bool $jecouteAccess): void
    {
        $this->jecouteAccess = $jecouteAccess;
    }

    public function setInseeCode(string $inseeCode): void
    {
        $this->inseeCode = $inseeCode;
    }

    public function getInseeCode(): ?string
    {
        return $this->inseeCode;
    }

    /**
     * @Assert\NotNull(message="La valeur saisie dans le champ Candidat Municipales 2020 🇫🇷 n'est pas un code INSEE de ville valide.")
     */
    public function getCityName(): ?string
    {
        return FranceCitiesBundle::getCityNameFromInseeCode((string) $this->inseeCode);
    }

    public function getDepartmentalCode(): string
    {
        return substr($this->inseeCode, 0, 2);
    }
}
