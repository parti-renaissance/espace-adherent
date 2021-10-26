<?php

namespace App\Doctrine\Hydrators;

use App\Entity\Pap\Address;
use Doctrine\ORM\Internal\Hydration\AbstractHydrator;
use Ramsey\Uuid\Uuid;

class PapAddressHydrator extends AbstractHydrator
{
    protected function hydrateAllData()
    {
        $result = [];
        foreach ($this->_stmt->fetchAll(\PDO::FETCH_ASSOC) as $row) {
            $this->hydrateRowData($row, $result);
        }

        return $result;
    }

    protected function hydrateRowData(array $row, array &$result)
    {
        if (!\count($row)) {
            return false;
        }

        $address = new Address(Uuid::fromString($row['uuid']));
        $address->setNumber($row['number']);
        $address->setAddress($row['address']);
        $address->setInseeCode($row['insee_code']);
        $address->setCityName($row['city_name']);
        $address->setLatitude($row['latitude']);
        $address->setLongitude($row['longitude']);

        $result[] = $address;
    }
}
