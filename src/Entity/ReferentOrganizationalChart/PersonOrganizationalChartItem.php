<?php

namespace AppBundle\Entity\ReferentOrganizationalChart;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class PersonOrganizationalChartItem extends AbstractOrganizationalChartItem
{
    /**
     * @var Collection|ReferentPersonLink[]
     *
     * @ORM\OneToMany(targetEntity="ReferentPersonLink", mappedBy="personOrganizationalChartItem", cascade={"persist"})
     */
    private $referentPersonLinks;

    public function __construct(string $label = null, AbstractOrganizationalChartItem $parent = null)
    {
        parent::__construct($label, $parent);

        $this->referentPersonLinks = new ArrayCollection();
    }

    public function getTypeLabel(): string
    {
        return 'Membre';
    }

    /**
     * @return Collection|ReferentPersonLink[]
     */
    public function getReferentPersonLinks(): Collection
    {
        return $this->referentPersonLinks;
    }

    public function setReferentPersonLinks(Collection $referentPersonLinks): void
    {
        $this->referentPersonLinks = $referentPersonLinks;
    }

    public function addReferentPersonLink(ReferentPersonLink $referentPersonLink): void
    {
        $referentPersonLink->setPersonOrganizationalChartItem($this);
        $this->referentPersonLinks->add($referentPersonLink);
    }

    public function removeReferentPersonLink(ReferentPersonLink $referentPersonLink): void
    {
        $referentPersonLink->setPersonOrganizationalChartItem(null);
        $this->referentPersonLinks->remove($referentPersonLink);
    }
}
