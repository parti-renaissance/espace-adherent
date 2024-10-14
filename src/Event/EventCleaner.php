<?php

namespace App\Event;

class EventCleaner
{
    private const ALLOWED_KEYS_FOR_DEEP = ['name', 'title', 'type', 'objectID', 'uuid', 'slug', 'status', 'visibility', 'category'];
    private const ALLOWED_KEYS = ['name', 'organizer', 'author', 'title', 'type', 'objectID', 'uuid', 'slug', 'time_zone', 'begin_at', 'finish_at', 'status', 'visibility', 'image', 'image_url', 'link', 'category', 'editable'];

    public function cleanEventData(array $eventData, bool $deep = false): array
    {
        foreach ($eventData as $key => $value) {
            if (!\in_array($key, $deep ? self::ALLOWED_KEYS_FOR_DEEP : self::ALLOWED_KEYS)) {
                $eventData[$key] = null;
            }
        }

        return $eventData;
    }
}
