<?php

declare(strict_types=1);

namespace App\Api\Provider\Hub;

use App\Entity\Action\Action;
use App\Entity\Event\Event;

final class HubItemRow
{
    public const string TYPE_EVENT = 'event';
    public const string TYPE_ACTION = 'action';

    public function __construct(
        public Event|Action $entity,
        public string $type,
        public int $priority,
        public int $timeToBegin,
        public ?float $distance,
        public \DateTimeInterface $beginAt,
        public \DateTimeInterface $createdAt,
        public ?\DateTimeInterface $finishAt,
        public int $participantsCount,
    ) {
    }
}
