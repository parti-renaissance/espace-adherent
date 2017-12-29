<?php

namespace AppBundle\Serializer;

use AppBundle\Entity\CitizenAction;
use JMS\Serializer\GraphNavigator;
use JMS\Serializer\Handler\SubscribingHandlerInterface;

class CitizenActionICalHandler implements SubscribingHandlerInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribingMethods(): array
    {
        return [
            [
                'type' => CitizenAction::class,
                'format' => 'ical',
                'direction' => GraphNavigator::DIRECTION_SERIALIZATION,
                'method' => 'serialize',
            ],
        ];
    }

    public function serialize(IcalSerializationVisitor $visitor, CitizenAction $citizenAction): void
    {
        $data = [
            'VEVENT' => [
                'UID' => $citizenAction->getUuid()->toString(),
                'SUMMARY' => $citizenAction->getName(),
                'DESCRIPTION' => $citizenAction->getDescription(),
                'DTSTART' => $this->formatDate($citizenAction->getBeginAt()),
                'DTEND' => $this->formatDate($citizenAction->getFinishAt()),
                'LOCATION' => $citizenAction->getInlineFormattedAddress(),
            ],
        ];

        if ($organizer = $citizenAction->getOrganizer()) {
            $data['ORGANIZER'] = sprintf(
                'CN="%s %s";mailto:%s',
                $organizer->getFirstName(),
                mb_strtoupper($organizer->getLastName()),
                $organizer->getEmailAddress()
            );
        }

        $visitor->setRoot($data);
    }

    private function formatDate(\DateTimeInterface $date): string
    {
        return $date->format('Ymd\THis');
    }
}
