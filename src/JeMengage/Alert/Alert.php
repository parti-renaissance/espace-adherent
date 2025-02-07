<?php

namespace App\JeMengage\Alert;

class Alert
{
    public const TYPE_ELECTION = 'election';
    public const TYPE_LIVE = 'live';

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

    public static function createLive(
        string $title,
        string $description,
        ?string $ctaLabel = null,
        ?string $ctaUrl = null,
    ): self {
        return new self(
            self::TYPE_LIVE,
            'Live',
            $title,
            $description,
            $ctaLabel,
            $ctaUrl,
        );
    }
}
