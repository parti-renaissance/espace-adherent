<?php

declare(strict_types=1);

namespace App\Action;

use App\Normalizer\DataCleaner;

class ActionCleaner
{
    private const ALLOWED_KEYS = [
        'uuid',
        'type',
        'status',
        'editable',
        'date',
        'post_address',
        'post_address' => ['city_name', 'country', 'postal_code'],
        'author',
        'author' => ['first_name', 'last_name'],
    ];

    public function __construct(private readonly DataCleaner $cleaner)
    {
    }

    public function cleanActionData(array $actionData): array
    {
        return $this->cleaner->clean($actionData, self::ALLOWED_KEYS, ['date']);
    }
}
