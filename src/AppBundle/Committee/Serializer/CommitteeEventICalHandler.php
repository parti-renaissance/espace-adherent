<?php

namespace AppBundle\Committee\Serializer;

use AppBundle\Entity\CommitteeEvent;
use AppBundle\Serializer\Visitor\IcalSerializationVisitor;
use JMS\Serializer\GraphNavigator;
use JMS\Serializer\Handler\SubscribingHandlerInterface;
use JMS\Serializer\SerializationContext;

class CommitteeEventICalHandler implements SubscribingHandlerInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribingMethods(): array
    {
        return [
            [
                'type' => CommitteeEvent::class,
                'format' => 'ical',
                'direction' => GraphNavigator::DIRECTION_SERIALIZATION,
                'method' => 'serialize',
            ],
        ];
    }

    /**
     * @param IcalSerializationVisitor $visitor
     * @param CommitteeEvent           $committeeEvent
     * @param array                    $type
     * @param SerializationContext     $context
     */
    public function serialize(IcalSerializationVisitor $visitor, CommitteeEvent $committeeEvent, array $type, SerializationContext $context): void
    {
        $eventData = [
            'VEVENT' => [
                'UID' => $committeeEvent->getUuid()->toString(),
                'SUMMARY' => $committeeEvent->getName(),
                'DESCRIPTION' => $committeeEvent->getDescription(),
                'DTSTART' => $committeeEvent->getBeginAt()->format(\DateTime::W3C),
                'DTEND' => $committeeEvent->getFinishAt()->format(\DateTime::W3C),
                'LOCATION' => $committeeEvent->getInlineFormattedAddress(),
            ],
        ];

        if ($organizer = $committeeEvent->getOrganizer()) {
            $eventData['VEVENT']['ORGANIZER'] = sprintf(
                'CN="%s %s";mailto:%s',
                $organizer->getFirstName(),
                mb_strtoupper($organizer->getLastName()),
                $organizer->getEmailAddress()
            );
        }

        $visitor->setRoot($eventData);
    }
}
