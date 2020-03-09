<?php

namespace AppBundle\Entity\Election;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use AppBundle\Entity\City;
use AppBundle\Entity\ElectionRound;
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
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\City")
     */
    private $city;

    /**
     * @var array
     *
     * @ORM\Column(type="json")
     */
    private $lists = [];

    public function __construct(City $city, ElectionRound $electionRound)
    {
        $this->city = $city;

        parent::__construct($electionRound);
    }

    public function isComplete(): bool
    {
        return parent::isComplete() && !empty($this->lists);
    }

    public function isPartial(): bool
    {
        return parent::isPartial() || !empty($this->lists);
    }

    public function getCity(): ?City
    {
        return $this->city;
    }

    public function setCity(City $city): void
    {
        $this->city = $city;
    }

    public function getLists(): array
    {
        return $this->lists;
    }

    public function setLists(array $lists): void
    {
        $this->lists = $lists;
    }

    public function addList(string $label, int $votes): void
    {
        $this->lists[] = ['label' => $label, 'votes' => $votes];
    }
}
