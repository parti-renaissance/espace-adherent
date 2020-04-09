<?php

namespace AppBundle\Entity\Election;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use AppBundle\Entity\City;
use AppBundle\Entity\ElectionRound;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\Election\MinistryVoteResultRepository")
 *
 * @Algolia\Index(autoIndex=false)
 */
class MinistryVoteResult extends BaseVoteResult
{
    /**
     * @var City
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\City")
     */
    private $city;

    /**
     * @var MinistryListTotalResult[]|Collection
     *
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Election\MinistryListTotalResult", mappedBy="ministryVoteResult", cascade={"all"}, orphanRemoval=true)
     */
    private $listTotalResults;

    public function __construct(City $city, ElectionRound $electionRound)
    {
        parent::__construct($electionRound);

        $this->city = $city;
        $this->listTotalResults = new ArrayCollection();
    }

    public function isComplete(): bool
    {
        return parent::isComplete()
            && !$this->listTotalResults
                ->filter(static function (MinistryListTotalResult $total) { return $total->getTotal() > 0; })
                ->isEmpty()
        ;
    }

    public function isPartial(): bool
    {
        return parent::isPartial()
            || !$this->listTotalResults
                ->filter(static function (MinistryListTotalResult $total) { return $total->getTotal() > 0; })
                ->isEmpty()
        ;
    }

    public function getCity(): City
    {
        return $this->city;
    }

    public function setCity(City $city): void
    {
        $this->city = $city;
    }

    /**
     * @return MinistryListTotalResult[]
     */
    public function getListTotalResults(): array
    {
        return $this->listTotalResults->toArray();
    }

    public function addListTotalResult(MinistryListTotalResult $result): void
    {
        if (!$this->listTotalResults->contains($result)) {
            $result->setMinistryVoteResult($this);
            $this->listTotalResults->add($result);
        }
    }

    public function removeListTotalResult(MinistryListTotalResult $result): void
    {
        $this->listTotalResults->removeElement($result);
    }

    public function findListWithLabel(string $label): ?MinistryListTotalResult
    {
        foreach ($this->listTotalResults as $list) {
            if ($list->getLabel() === $label) {
                return $list;
            }
        }

        return null;
    }
}
