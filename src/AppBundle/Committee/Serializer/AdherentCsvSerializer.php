<?php

namespace AppBundle\Committee\Serializer;

use AppBundle\Entity\Adherent;
use AppBundle\Exception\AdherentCollectionException;

class AdherentCsvSerializer
{
    /**
     * @param mixed $adherents
     *
     * @return string
     *
     * @throws AdherentCollectionException
     */
    public static function serialize($adherents): string
    {
        if (!is_iterable($adherents)) {
            throw new AdherentCollectionException();
        }

        $handle = fopen('php://memory', 'r+');
        fputcsv($handle, ['Prénom', 'Nom', 'Age', 'Code postal', 'Ville']);

        foreach ($adherents as $adherent) {
            if (!$adherent instanceof Adherent) {
                throw new AdherentCollectionException();
            }

            fputcsv($handle, [
                $adherent->getFirstName(),
                $adherent->getLastNameInitial(),
                $adherent->getAge(),
                $adherent->getPostalCode(),
                $adherent->getCityName(),
            ]);
        }

        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        return $csv;
    }
}
