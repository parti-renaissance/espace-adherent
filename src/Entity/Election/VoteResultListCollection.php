<?php

namespace AppBundle\Entity\Election;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\Election\VoteResultListCollectionRepository")
 */
class VoteResultListCollection
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
     * @var VoteResultListCollectionCityProxy[]|Collection
     *
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Election\VoteResultListCollectionCityProxy", mappedBy="listCollection", cascade={"all"}, orphanRemoval=true)
     */
    private $cityProxies;

    /**
     * @var VoteResultList[]|Collection
     *
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Election\VoteResultList", mappedBy="listCollection", cascade={"all"}, orphanRemoval=true)
     *
     * @Assert\Count(min=1)
     */
    private $lists;

    public function __construct()
    {
        $this->cityProxies = new ArrayCollection();
        $this->lists = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCityProxies(): array
    {
        return $this->cityProxies->toArray();
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

    public function mergeCities(array $cities): void
    {
        foreach ($cities as $city) {
            $found = false;

            foreach ($this->cityProxies as $proxy) {
                if ($proxy->getCity()->equals($city)) {
                    $found = true;
                    break;
                }
            }

            if (!$found) {
                $this->cityProxies->add(new VoteResultListCollectionCityProxy($this, $city));
            }
        }
    }
}
