<?php

namespace App\JeMengage\Alert;

class Alert
{
    public function __construct(
        public readonly string $label,
        public readonly string $title,
        public readonly string $description,
        public readonly ?string $ctaLabel = null,
        public readonly ?string $ctaUrl = null,
    ) {
    }
}
