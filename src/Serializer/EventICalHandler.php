<?php

namespace AppBundle\Serializer;

use AppBundle\Entity\Event;
use AppBundle\Entity\MunicipalEvent;
use JMS\Serializer\GraphNavigator;
use JMS\Serializer\Handler\SubscribingHandlerInterface;

class EventICalHandler implements SubscribingHandlerInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribingMethods(): array
    {
        return [
            [
                'type' => Event::class,
                'format' => 'ical',
                'direction' => GraphNavigator::DIRECTION_SERIALIZATION,
                'method' => 'serialize',
            ],
            [
                'type' => MunicipalEvent::class,
                'format' => 'ical',
                'direction' => GraphNavigator::DIRECTION_SERIALIZATION,
                'method' => 'serialize',
            ],
        ];
    }

    public function serialize(IcalSerializationVisitor $visitor, Event $event): void
    {
        $eventData = [
            'VEVENT' => [
                'UID' => $event->getUuid()->toString(),
                'SUMMARY' => $event->getName(),
                'DESCRIPTION' => $event->getDescription(),
                'DTSTART' => $this->formatDate($event->getLocalBeginAt(), $event->getTimeZone()),
                'DTEND' => $this->formatDate($event->getLocalFinishAt(), $event->getTimeZone()),
                'LOCATION' => $event->getInlineFormattedAddress(),
            ],
        ];

        if ($organizer = $event->getOrganizer()) {
            $eventData['ORGANIZER'] = sprintf(
                'CN="%s %s";mailto:%s',
                $organizer->getFirstName(),
                mb_strtoupper($organizer->getLastName()),
                $organizer->getEmailAddress()
            );
        }

        $visitor->setRoot($eventData);
    }

    private function formatDate(\DateTimeInterface $date, string $timezone): string
    {
        return sprintf('%s:%s', $timezone, $date->format('Ymd\THis'));
    }
}
