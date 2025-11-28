<?php

declare(strict_types=1);

namespace App\Entity;

use App\Collection\ZoneCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
class RepublicanSilence
{
    use EntityZoneTrait;

    /**
     * @var int
     */
    #[ORM\Column(type: 'integer', options: ['unsigned' => true])]
    #[ORM\GeneratedValue]
    #[ORM\Id]
    private $id;

    /**
     * @var \DateTime
     */
    #[Assert\NotBlank]
    #[ORM\Column(type: 'datetime')]
    private $beginAt;

    /**
     * @var \DateTime
     */
    #[Assert\Expression('value > this.getBeginAt()', message: 'committee.event.invalid_date_range')]
    #[Assert\NotBlank]
    #[ORM\Column(type: 'datetime')]
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
