<?php

namespace AppBundle\Serializer;

use AppBundle\Entity\Event;
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
        ];
    }

    /**
     * @param IcalSerializationVisitor $visitor
     * @param Event                    $event
     */
    public function serialize(IcalSerializationVisitor $visitor, Event $event): void
    {
        $eventData = [
            'VEVENT' => [
                'UID' => $event->getUuid()->toString(),
                'SUMMARY' => $event->getName(),
                'DESCRIPTION' => $event->getDescription(),
                'DTSTART' => $this->formatDate($event->getBeginAt()),
                'DTEND' => $this->formatDate($event->getFinishAt()),
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

    /**
     * @param \DateTime $date
     *
     * @return string
     */
    private function formatDate(\DateTime $date): string
    {
        return $date->format('Ymd\THis');
    }
}
