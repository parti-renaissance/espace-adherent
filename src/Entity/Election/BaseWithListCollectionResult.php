<?php

namespace App\Entity\Election;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use App\Entity\ElectionRound;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 *
 * @ORM\Table(
 *     name="vote_result",
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(name="city_vote_result_city_round_unique", columns={"city_id", "election_round_id"}),
 *         @ORM\UniqueConstraint(name="vote_place_result_city_round_unique", columns={"vote_place_id", "election_round_id"}),
 *     }
 * )
 *
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="type", type="string")
 * @ORM\DiscriminatorMap({
 *     "city": "CityVoteResult",
 *     "vote_place": "VotePlaceResult"
 * })
 *
 * @Algolia\Index(autoIndex=false)
 */
abstract class BaseWithListCollectionResult extends BaseVoteResult
{
    /**
     * @var ListTotalResult[]|Collection
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Election\ListTotalResult", mappedBy="voteResult", cascade={"all"})
     */
    private $listTotalResults;

    public function __construct(ElectionRound $electionRound)
    {
        parent::__construct($electionRound);

        $this->listTotalResults = new ArrayCollection();
    }

    public function isComplete(): bool
    {
        return parent::isComplete()
            && !$this->listTotalResults
                ->filter(static function (ListTotalResult $total) { return $total->getTotal() > 0; })
                ->isEmpty()
            ;
    }

    public function isPartial(): bool
    {
        return parent::isPartial()
            || !$this->listTotalResults
                ->filter(static function (ListTotalResult $total) { return $total->getTotal() > 0; })
                ->isEmpty()
            ;
    }

    /**
     * @return ListTotalResult[]
     */
    public function getListTotalResults(): array
    {
        return $this->listTotalResults->toArray();
    }

    public function updateLists(VoteResultListCollection $listCollection): void
    {
        $currentLists = $this->listTotalResults->map(static function (ListTotalResult $totalResult) {
            return $totalResult->getList();
        });

        foreach ($listCollection->getLists() as $newListToAdd) {
            if (!$currentLists->contains($newListToAdd)) {
                $listTotalResult = new ListTotalResult($newListToAdd);
                $listTotalResult->setVoteResult($this);

                $this->listTotalResults->add($listTotalResult);
            }
        }
    }

    public function setListTotalResults(array $listTotalResults): void
    {
        $this->listTotalResults = new ArrayCollection($listTotalResults);
    }
}
