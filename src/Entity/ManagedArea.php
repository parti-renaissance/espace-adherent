<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\MappedSuperclass
 */
abstract class ManagedArea
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue
     */
    private $id;

    /**
     * @var array
     *
     * @ORM\Column(type="simple_array", nullable=true)
     */
    private $codes;

    protected $adherent;

    public function getId()
    {
        return $this->id;
    }

    public function getCodes(): array
    {
        return $this->codes;
    }

    public function setCodes(array $codes)
    {
        $this->codes = $codes;
    }

    public function getCodesAsString(): string
    {
        return implode(', ', $this->codes);
    }

    public function setCodesAsString(?string $codes)
    {
        $this->codes = $codes ? array_map('trim', explode(',', $codes)) : [];
    }

    public function getAdherent(): ?Adherent
    {
        return $this->adherent;
    }

    public function setAdherent(Adherent $adherent = null)
    {
        $this->adherent = $adherent;
    }
}
