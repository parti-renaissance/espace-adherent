<?php

namespace App\Entity;

use App\Collection\ZoneCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
class RepublicanSilence
{
    use EntityZoneTrait;

    /**
     * @var int
     */
    #[ORM\Id]
    #[ORM\Column(type: 'integer', options: ['unsigned' => true])]
    #[ORM\GeneratedValue]
    private $id;

    /**
     * @var \DateTime
     */
    #[Groups(['read_api'])]
    #[ORM\Column(type: 'datetime')]
    #[Assert\NotBlank]
    private $beginAt;

    /**
     * @var \DateTime
     */
    #[Groups(['read_api'])]
    #[ORM\Column(type: 'datetime')]
    #[Assert\NotBlank]
    #[Assert\Expression('value > this.getBeginAt()', message: 'committee.event.invalid_date_range')]
    private $finishAt;

    public function __construct()
    {
        $this->zones = new ZoneCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getBeginAt(): ?\DateTime
    {
        return $this->beginAt;
    }

    public function setBeginAt(\DateTime $beginAt): void
    {
        $this->beginAt = $beginAt;
    }

    public function getFinishAt(): ?\DateTime
    {
        return $this->finishAt;
    }

    public function setFinishAt(\DateTime $finishAt): void
    {
        $this->finishAt = $finishAt;
    }
}
