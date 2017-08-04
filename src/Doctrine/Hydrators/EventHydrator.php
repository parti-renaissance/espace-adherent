<?php
namespace AppBundle\Doctrine\Hydrators;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\Committee;
use AppBundle\Entity\Event;
use AppBundle\Entity\EventCategory;
use AppBundle\Entity\PostAddress;
use Doctrine\ORM\Internal\Hydration\AbstractHydrator;
use Ramsey\Uuid\Uuid;

class EventHydrator extends AbstractHydrator
{
    protected function hydrateAllData()
    {
        $result = array();
        foreach ($this->_stmt->fetchAll(\PDO::FETCH_ASSOC) as $row) {
            $this->hydrateRowData($row, $result);
        }

        return $result;
    }

    protected function hydrateRowData(array $row, array &$result)
    {
        if (count($row) == 0) {
            return false;
        }

        $uuid = Uuid::fromString($row['uuid']);
        $organizer = $this->_em->getRepository(Adherent::class)->findOneBy(['id' => $row['organizer_id']]);
        $committee = $this->_em->getRepository(Committee::class)->findOneBy(['id' => $row['committee_id']]);
        if ('FR' === $row['address_country']) {
            $address = PostAddress::createFrenchAddress($row['address_address'], $row['address_city_insee'], $row['address_latitude'], $row['address_longitude']);
        } else {

            $address = PostAddress::createForeignAddress($row['address_country'], $row['address_postal_code'], $row['address_city_name'], $row['address_address'], $row['address_latitude'], $row['address_longitude']);
        }

        $event = new Event(
            $uuid,
            $organizer,
            $committee,
            $row['name'],
            new EventCategory(),
            $row['description'],
            $address,
            $row['begin_at'],
            $row['finish_at'],
            $row['capacity'],
            $row['is_for_legislatives'],
            $row['slug'],
            $row['created_at'],
            $row['participants_count'],
            $row['type']
        );

        $result[] = $event;
    }
}
