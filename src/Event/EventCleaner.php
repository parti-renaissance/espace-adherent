<?php

namespace App\Event;

class EventCleaner
{
    public function cleanEventData(array $eventData): array
    {
        foreach ($eventData as $key => $value) {
            if (!\in_array($key, ['name', 'title', 'type', 'objectID', 'uuid', 'slug', 'time_zone', 'begin_at', 'finish_at', 'status', 'visibility', 'image', 'image_url', 'link', 'category'])) {
                $eventData[$key] = null;
            }
        }

        return $eventData;
    }
}
