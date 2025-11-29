<?php

declare(strict_types=1);

namespace App\Doctrine\Hydrators;

use App\Address\AddressInterface;
use App\Entity\Adherent;
use App\Entity\Committee;
use App\Entity\Event\Event;
use App\Entity\Event\EventCategory;
use App\Entity\NullablePostAddress;
use App\Entity\PostAddress;
use Doctrine\ORM\Internal\Hydration\AbstractHydrator;
use Ramsey\Uuid\Uuid;

class EventHydrator extends AbstractHydrator
{
    protected function hydrateAllData(): mixed
    {
        $result = [];
        foreach ($this->_stmt->fetchAllAssociative() as $row) {
            $this->hydrateRowData($row, $result);
        }

        return $result;
    }

    protected function hydrateRowData(array $row, array &$result): void
    {
        if (!\count($row)) {
            return;
        }

        if (AddressInterface::FRANCE === $row['event_address_country']) {
            $addressEvent = $this->createFrenchAddress($row['event_address_address'], $row['event_address_city_insee'], $row['event_address_city_name'], $row['event_address_latitude'], $row['event_address_longitude']);
        } else {
            $addressEvent = $this->createForeignAddress($row['event_address_country'], $row['event_address_postal_code'], $row['event_address_city_name'], $row['event_address_address'], $row['event_address_latitude'], $row['event_address_longitude']);
        }

        $addressCommittee = null;
        if (AddressInterface::FRANCE === $row['committee_address_country']) {
            $addressCommittee = $this->createFrenchAddress($row['committee_address_address'], $row['committee_address_city_insee'], $row['committee_address_city_name'], $row['committee_address_latitude'], $row['committee_address_longitude']);
        } elseif ($row['committee_address_country']) {
            $addressCommittee = $this->createForeignAddress($row['committee_address_country'], $row['committee_address_postal_code'], $row['committee_address_city_name'], $row['committee_address_address'], $row['committee_address_latitude'], $row['committee_address_longitude']);
        }

        //        if (AddressInterface::FRANCE === $row['adherent_address_country']) {
        //            $addressAdherent = $this->createFrenchAddress($row['adherent_address_address'], $row['adherent_address_city_insee'], $row['adherent_address_city_name'], $row['adherent_address_latitude'], $row['adherent_address_longitude']);
        //        } else {
        //            $addressAdherent = $this->createForeignAddress($row['adherent_address_country'], $row['adherent_address_postal_code'], $row['adherent_address_city_name'], $row['adherent_address_address'], $row['adherent_address_latitude'], $row['adherent_address_longitude']);
        //        }

        $uuidEvent = Uuid::fromString($row['event_uuid']);
        $committee = null;
        if ($row['committee_uuid']) {
            $uuidCommittee = Uuid::fromString($row['committee_uuid']);
            $uuidCommitteeOrganizer = $row['committee_created_by'] ? Uuid::fromString($row['committee_created_by']) : $uuidEvent; // to fix the problem on staging where committee can be without creator; this value is used only for 'new Committee()'
            $committee = new Committee($uuidCommittee, $uuidCommitteeOrganizer, $row['committee_name'], $row['committee_description'], $addressCommittee, null, $row['committee_slug']);
        }

        $organizer = null;
        if ($uuidOrganizer = $row['adherent_uuid'] ? Uuid::fromString($row['adherent_uuid']) : null) {
            $organizer = Adherent::create(
                $uuidOrganizer,
                $row['adherent_public_id'],
                $row['adherent_email_address'],
                $uuidOrganizer->toString(),
                $row['adherent_gender'],
                $row['adherent_first_name'],
                $row['adherent_last_name'],
                new \DateTime($row['adherent_birthdate']),
                $row['adherent_position']
            );
        }

        $event = new Event($uuidEvent);
        $event->setAuthor($organizer);
        $event->setCommittee($committee);
        $event->setName($row['event_name']);
        $event->setDescription($row['event_description']);
        $event->setCategory(new EventCategory($row['event_category_name']));
        $event->setPostAddress($addressEvent);
        $event->setBeginAt(new \DateTime($row['event_begin_at']));
        $event->setFinishAt(new \DateTime($row['event_finish_at']));
        $event->setCapacity($row['event_capacity']);
        $event->setCreatedAt(new \DateTime($row['event_created_at']));
        $event->setTimeZone($row['timeZone']);
        $event->setSlug($row['event_slug']);

        $result[] = $event;
    }

    private function createFrenchAddress(
        ?string $street,
        ?string $cityCode,
        ?string $cityName,
        ?float $latitude,
        ?float $longitude,
    ): NullablePostAddress {
        return NullablePostAddress::createFrenchAddress($street ?? '', $cityCode ?? '-', $cityName ?? '', null, null, $latitude, $longitude);
    }

    private function createForeignAddress(
        ?string $country,
        ?string $zipCode,
        ?string $cityName,
        ?string $street,
        ?float $latitude,
        ?float $longitude,
    ): PostAddress {
        return PostAddress::createForeignAddress($country ?? '', $zipCode ?? '', $cityName, $street ?? '', null, null, $latitude, $longitude);
    }
}
