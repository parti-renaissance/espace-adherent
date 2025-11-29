<?php

declare(strict_types=1);

namespace App\JeMengage\Alert;

use App\Entity\AppAlert;
use App\Entity\Event\Event;
use App\Entity\NationalEvent\NationalEvent;
use Symfony\Component\Serializer\Attribute\Ignore;

class Alert
{
    // Date used to sort alerts
    #[Ignore]
    public ?\DateTimeInterface $date = null;

    public function __construct(
        public readonly AlertTypeEnum $type,
        public readonly string $label,
        public readonly string $title,
        public readonly string $description,
        public readonly ?string $ctaLabel = null,
        public readonly ?string $ctaUrl = null,
        public readonly ?string $imageUrl = null,
        public readonly ?string $shareUrl = null,
        public readonly ?array $data = null,
    ) {
    }

    public static function createElection(
        string $title,
        string $description,
        ?string $ctaLabel = null,
        ?string $ctaUrl = null,
    ): self {
        return new self(
            AlertTypeEnum::ELECTION,
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
            $event->getBeginAt() < $now ? AlertTypeEnum::LIVE : AlertTypeEnum::LIVE_ANNOUNCE,
            $event->getBeginAt() < $now ? 'En direct' : 'En direct à '.$event->getBeginAt()->format('H\hi'),
            $event->getName(),
            '',
            'Voir',
            $url,
        );
    }

    public static function createMeeting(NationalEvent $event, string $ctaLabel, ?string $ctaUrl, ?string $imageUrl = null, ?string $shareUrl = null, ?array $data = null): self
    {
        return new self(
            AlertTypeEnum::MEETING,
            'Grand rassemblement',
            $event->alertTitle ?? $event->getName(),
            (string) $event->alertDescription,
            $ctaLabel,
            $ctaUrl,
            $imageUrl,
            $shareUrl,
            $data,
        );
    }

    public static function createFromAppAlert(AppAlert $appAlert, ?string $ctaUrl): self
    {
        return new self(
            $appAlert->type,
            $appAlert->label,
            $appAlert->title,
            $appAlert->description,
            $appAlert->ctaLabel,
            $ctaUrl ?? $appAlert->ctaUrl,
            $appAlert->imageUrl,
            $appAlert->shareUrl,
            $appAlert->data,
        );
    }
}
