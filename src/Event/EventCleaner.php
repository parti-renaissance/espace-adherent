<?php

namespace App\Event;

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
        'uuid',
        'slug',
        'time_zone',
        'begin_at',
        'status',
        'visibility',
        'category',
        'mode',
        'editable',
        'user_registered_at',
    ];

    public function cleanEventData(array $eventData, array $allowedKeys = self::ALLOWED_KEYS): array
    {
        foreach ($eventData as $key => $value) {
            if (!\in_array($key, $allowedKeys)) {
                $eventData[$key] = null;
                continue;
            }

            if (\is_array($value) && !empty($allowedKeys[$key]) && \is_array($allowedKeys[$key])) {
                $eventData[$key] = $this->cleanEventData($value, $allowedKeys[$key]);
                continue;
            }

            if (str_ends_with($key, '_at')) {
                $eventData[$key] = substr($value, 0, 10);
            }
        }

        return $eventData;
    }
}
