<?php

namespace AppBundle\Membership;

use AppBundle\Entity\Unregistration;

class UnregistrationSerializer
{
    /**
     * @param Unregistration[] $unregistrations
     *
     * @return string
     */
    public function serialize(array $unregistrations): string
    {
        if (!is_iterable($unregistrations)) {
            throw new \InvalidArgumentException();
        }

        $handle = fopen('php://memory', 'rb+');
        foreach ($unregistrations as $unregistration) {
            fputcsv($handle, [
                'uuid' => $unregistration->getUuid(),
                'postalCode' => $unregistration->getPostalCode(),
                'reasons' => implode(',', $unregistration->getReasons()),
                'comment' => $unregistration->getComment(),
                'registeredAt' => $unregistration->getRegisteredAt()->format('Y-m-d H:i:s'),
                'unregisteredAt' => $unregistration->getUnregisteredAt()->format('Y-m-d H:i:s'),
            ]);
        }

        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        return $csv;
    }
}
