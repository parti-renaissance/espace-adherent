<?php

namespace App\JeMengage\Alert;

use App\Entity\Event\Event;

class Alert
{
    public const TYPE_ELECTION = 'election';
    public const TYPE_LIVE = 'live';
    public const TYPE_LIVE_ANNOUNCE = 'live_announce';

    public function __construct(
        public readonly string $type,
        public readonly string $label,
        public readonly string $title,
        public readonly string $description,
        public readonly ?string $ctaLabel = null,
        public readonly ?string $ctaUrl = null,
    ) {
    }

    public static function createElection(
        string $title,
        string $description,
        ?string $ctaLabel = null,
        ?string $ctaUrl = null,
    ): self {
        return new self(
            self::TYPE_ELECTION,
            'Consultation / Élection',
            $title,
            $description,
            $ctaLabel,
            $ctaUrl,
        );
    }

    public static function createLive(Event $event): self
    {
        $now = new \DateTimeImmutable();

        return new self(
            $event->getBeginAt() < $now ? self::TYPE_LIVE : self::TYPE_LIVE_ANNOUNCE,
            $event->getBeginAt() < $now ? 'En direct' : 'En direct à '.$event->getBeginAt()->format('H\hi'),
            $event->getName(),
            '',
            'Voir',
            '/evenements/'.$event->getSlug(),
        );
    }
}
