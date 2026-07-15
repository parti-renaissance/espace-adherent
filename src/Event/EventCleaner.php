<?php

declare(strict_types=1);

namespace App\Event;

use App\Normalizer\DataCleaner;

class EventCleaner
{
    private const ALLOWED_KEYS = [
        'name',
        'title',

        'author',
        'author_instance',
        'author_role',
        'author_zone',
        'author' => [
            'first_name',
            'last_name',
            'instance',
            'role',
            'zone',
            'image_url',
        ],
        'organizer',
        'organizer' => [
            'first_name',
            'last_name',
            'instance',
            'role',
            'zone',
            'image_url',
        ],

        'post_address',
        'post_address' => [
            'city_name',
            'country',
            'postal_code',
        ],

        'type',
        'objectID',
        'identifier',
        'uuid',
        'slug',
        'time_zone',
        'begin_at',
        'status',
        'visibility',
        'category',
        'is_national',
        'mode',
        'editable',
        'hidden',
        'pinned',
        'mobile_app_only',
        'user_registered_at',
    ];

    public function __construct(private readonly DataCleaner $cleaner)
    {
    }

    public function cleanEventData(array $eventData, array $allowedKeys = self::ALLOWED_KEYS): array
    {
        return $this->cleaner->clean($eventData, $allowedKeys);
    }
}
