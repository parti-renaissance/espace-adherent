<?php

namespace App\Entity\Election;

use App\Entity\City;
use App\Entity\ElectionRound;
use App\Repository\Election\VoteResultListCollectionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: VoteResultListCollectionRepository::class)]
class VoteResultListCollection
{
    /**
     * @var int|null
     */
    #[ORM\Column(type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    private $id;

    /**
     * @var City
     */
    #[ORM\ManyToOne(targetEntity: City::class)]
    private $city;

    /**
     * @var VoteResultList[]|Collection
     *
     * @Assert\Count(min=1)
     */
    #[ORM\OneToMany(mappedBy: 'listCollection', targetEntity: VoteResultList::class, cascade: ['all'], orphanRemoval: true)]
    private $lists;

    /**
     * @var ElectionRound
     */
    #[ORM\ManyToOne(targetEntity: ElectionRound::class)]
    private $electionRound;

    public function __construct(City $city, ElectionRound $electionRound)
    {
        $this->city = $city;
        $this->electionRound = $electionRound;

        $this->lists = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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
     * @return VoteResultList[]
     */
    public function getLists(): array
    {
        return $this->lists->toArray();
    }

    public function addList(VoteResultList $list): void
    {
        if (!$this->lists->contains($list)) {
            $list->setListCollection($this);
            $this->lists->add($list);
        }
    }

    public function removeList(VoteResultList $list): void
    {
        $this->lists->removeElement($list);
    }

    public function containsList(string $listLabel): bool
    {
        foreach ($this->lists as $list) {
            if (0 === strcasecmp($list->getLabel(), $listLabel)) {
                return true;
            }
        }

        return false;
    }

    public function getElectionRound(): ElectionRound
    {
        return $this->electionRound;
    }

    public function setElectionRound(ElectionRound $electionRound): void
    {
        $this->electionRound = $electionRound;
    }
}
