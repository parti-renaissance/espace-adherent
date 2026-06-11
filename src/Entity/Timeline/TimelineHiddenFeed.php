<?php

declare(strict_types=1);

namespace App\Entity\Timeline;

use App\Entity\EntityIdentityTrait;
use App\Repository\Timeline\TimelineHiddenFeedRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: TimelineHiddenFeedRepository::class)]
class TimelineHiddenFeed
{
    use EntityIdentityTrait;

    #[ORM\Column(type: 'datetime_immutable')]
    public \DateTimeImmutable $hiddenAt;

    public function __construct(Uuid $uuid)
    {
        $this->uuid = $uuid;
        $this->hiddenAt = new \DateTimeImmutable();
    }
}
