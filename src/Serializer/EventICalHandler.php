<?php

namespace AppBundle\Serializer;

use AppBundle\Entity\BaseEvent;
use AppBundle\Entity\CitizenAction;
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
            [
                'type' => CitizenAction::class,
                'format' => 'ical',
                'direction' => GraphNavigator::DIRECTION_SERIALIZATION,
                'method' => 'serialize',
            ],
        ];
    }

    public function serialize(IcalSerializationVisitor $visitor, BaseEvent $event): void
    {
        $eventData = [
            'VEVENT' => [
                'UID' => $event->getUuid()->toString(),
                'SUMMARY' => $event->getName(),
                'DESCRIPTION' => $event->getDescription(),
                'DTSTART' => $event->getLocalBeginAt(),
                'DTEND' => $event->getLocalFinishAt(),
                'LOCATION' => $event->getInlineFormattedAddress(),
            ],
        ];

        if ($organizer = $event->getOrganizer()) {
            $eventData['ORGANIZER'] = sprintf(
                '%s %s',
                $organizer->getFirstName(),
                mb_strtoupper($organizer->getLastName())
            );
        }

        $visitor->setRoot($eventData);
    }
}
