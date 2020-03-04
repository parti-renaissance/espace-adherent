<?php

namespace AppBundle\Entity;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * @ORM\Entity
 *
 * @Algolia\Index(autoIndex=false)
 */
class CityVoteResult
{
    use TimestampableEntity;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue
     */
    private $id;

    /**
     * @var City
     *
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\City")
     */
    private $city;

    /**
     * @var ElectionRound
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\ElectionRound")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $electionRound;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    private $registered = 0;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    private $abstentions = 0;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    private $voters = 0;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    private $expressed = 0;

    /**
     * @var array
     *
     * @ORM\Column(type="json")
     */
    private $lists = [];

    /**
     * @var Adherent|null
     *
     * @Gedmo\Blameable(on="create")
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Adherent")
     */
    protected $createdBy;

    /**
     * @var Adherent|null
     *
     * @Gedmo\Blameable(on="update")
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Adherent")
     */
    protected $updatedBy;

    public function __construct(City $city, ElectionRound $electionRound)
    {
        $this->city = $city;
        $this->electionRound = $electionRound;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getElectionRound(): ?ElectionRound
    {
        return $this->electionRound;
    }

    public function setElectionRound(ElectionRound $electionRound): void
    {
        $this->electionRound = $electionRound;
    }

    public function getRegistered(): ?int
    {
        return $this->registered;
    }

    public function setRegistered(int $registered): void
    {
        $this->registered = $registered;
    }

    public function getAbstentions(): ?int
    {
        return $this->abstentions;
    }

    public function setAbstentions(int $abstentions): void
    {
        $this->abstentions = $abstentions;
    }

    public function getVoters(): ?int
    {
        return $this->voters;
    }

    public function setVoters(int $voters): void
    {
        $this->voters = $voters;
    }

    public function getExpressed(): ?int
    {
        return $this->expressed;
    }

    public function setExpressed(int $expressed): void
    {
        $this->expressed = $expressed;
    }

    public function getAbstentionsPercentage(): ?float
    {
        if (0 === $this->registered) {
            return null;
        }

        return ($this->abstentions / $this->registered) * 100;
    }

    public function getExpressedPercentage(): ?float
    {
        if (0 === $this->registered) {
            return null;
        }

        return ($this->expressed / $this->registered) * 100;
    }

    public function getVotersPercentage(): ?float
    {
        if (0 === $this->registered) {
            return null;
        }

        return ($this->voters / $this->registered) * 100;
    }

    public function isComplete(): bool
    {
        return $this->registered && $this->abstentions && $this->expressed && $this->voters && !empty($this->lists);
    }

    public function isPartial(): bool
    {
        return $this->registered || $this->abstentions || $this->expressed || $this->voters || !empty($this->lists);
    }

    public function getCreatedBy(): ?Adherent
    {
        return $this->createdBy;
    }

    public function setCreatedBy(?Adherent $createdBy): void
    {
        $this->createdBy = $createdBy;
    }

    public function getUpdatedBy(): ?Adherent
    {
        return $this->updatedBy;
    }

    public function setUpdatedBy(?Adherent $updatedBy): void
    {
        $this->updatedBy = $updatedBy;
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
