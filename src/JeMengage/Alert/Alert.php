<?php

namespace App\JeMengage\Alert;

use App\Entity\Event\Event;
use App\Entity\NationalEvent\NationalEvent;

class Alert
{
    public const TYPE_ELECTION = 'election';
    public const TYPE_LIVE = 'live';
    public const TYPE_LIVE_ANNOUNCE = 'live_announce';
    public const TYPE_MEETING = 'meeting';

    public function __construct(
        public readonly string $type,
        public readonly string $label,
        public readonly string $title,
        public readonly string $description,
        public readonly ?string $ctaLabel = null,
        public readonly ?string $ctaUrl = null,
        public readonly ?string $imageUrl = null,
        public readonly ?string $shareUrl = null,
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

    public static function createLive(Event $event, string $url): self
    {
        $now = new \DateTimeImmutable();

        return new self(
            $event->getBeginAt() < $now ? self::TYPE_LIVE : self::TYPE_LIVE_ANNOUNCE,
            $event->getBeginAt() < $now ? 'En direct' : 'En direct à '.$event->getBeginAt()->format('H\hi'),
            $event->getName(),
            '',
            'Voir',
            $url,
        );
    }

    public static function createMeeting(NationalEvent $event, string $ctaLabel, string $ctaUrl, ?string $imageUrl = null, ?string $shareUrl = null): self
    {
        return new self(
            self::TYPE_MEETING,
            'Grand rassemblement',
            $event->alertTitle ?? $event->getName(),
            (string) $event->alertDescription,
            $ctaLabel,
            $ctaUrl,
            $imageUrl,
            $shareUrl
        );
    }
}
