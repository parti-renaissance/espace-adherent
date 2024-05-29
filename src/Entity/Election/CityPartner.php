<?php

namespace App\Entity\Election;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Table(name: 'election_city_partner')]
#[ORM\Entity]
class CityPartner
{
    public const CONSENSUS = 'consensus';
    public const DISSENSUS = 'dissensus';

    public const CONSENSUS_CHOICES = [
        self::CONSENSUS,
        self::DISSENSUS,
    ];

    /**
     * @var int|null
     */
    #[ORM\Column(type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    private $id;

    /**
     * @var string|null
     *
     * @Assert\NotBlank
     * @Assert\Length(max=255)
     */
    #[ORM\Column]
    private $label;

    /**
     * @var string|null
     *
     * @Assert\Choice(choices=CityPartner::CONSENSUS_CHOICES)
     */
    #[ORM\Column(nullable: true)]
    private $consensus;

    /**
     * @var CityCard|null
     */
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: CityCard::class, inversedBy: 'partners')]
    private $city;

    public function __construct(?CityCard $city = null, ?string $label = null, ?string $consensus = null)
    {
        $this->city = $city;
        $this->label = $label;
        $this->consensus = $consensus;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(?string $label): void
    {
        $this->label = $label;
    }

    public function getConsensus(): ?string
    {
        return $this->consensus;
    }

    public function setConsensus(?string $consensus): void
    {
        $this->consensus = $consensus;
    }

    public function getCity(): ?CityCard
    {
        return $this->city;
    }

    public function setCity(?CityCard $city): void
    {
        $this->city = $city;
    }
}
