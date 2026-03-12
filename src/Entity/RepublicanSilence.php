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
     * @var \DateTimeImmutable
     */
    #[Assert\NotBlank]
    #[ORM\Column(type: 'datetime_immutable')]
    private $beginAt;

    /**
     * @var \DateTimeImmutable
     */
    #[Assert\Expression('value > this.getBeginAt()', message: 'committee.event.invalid_date_range')]
    #[Assert\NotBlank]
    #[ORM\Column(type: 'datetime_immutable')]
    private $finishAt;

    public function __construct()
    {
        $this->zones = new ZoneCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getBeginAt(): ?\DateTimeImmutable
    {
        return $this->beginAt;
    }

    public function setBeginAt(\DateTimeImmutable $beginAt): void
    {
        $this->beginAt = $beginAt;
    }

    public function getFinishAt(): ?\DateTimeImmutable
    {
        return $this->finishAt;
    }

    public function setFinishAt(\DateTimeImmutable $finishAt): void
    {
        $this->finishAt = $finishAt;
    }
}
