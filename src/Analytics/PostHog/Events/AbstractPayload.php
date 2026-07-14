<?php

declare(strict_types=1);

namespace App\Analytics\PostHog\Events;

/**
 * Base class pour les payloads value-objects typés PostHog.
 * Chaque payload event porte ses propriétés typées + une méthode toArray()
 * qui sérialise pour l'envoi au PostHogService.
 */
abstract class AbstractPayload
{
    /** @return array<string, mixed> */
    abstract public function toArray(): array;
}
