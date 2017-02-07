<?php

namespace AppBundle\Committee\Serializer;

use AppBundle\Collection\AdherentCollection;
use AppBundle\Entity\Adherent;

class AdherentCsvSerializer
{
    /**
     * @param array|AdherentCollection $adherents
     *
     * @return string
     *
     * @throws \BadMethodCallException
     */
    public static function serialize($adherents): string
    {
        if (!is_iterable($adherents)) {
            throw new \BadMethodCallException('This method requires a collection of Adherent entities');
        }

        $handle = fopen('php://memory', 'r+');
        fputcsv($handle, ['Prénom', 'Nom', 'Age', 'Ville', 'Pays', 'Adresse email']);

        foreach ($adherents as $adherent) {
            if (!$adherent instanceof Adherent) {
                throw new \BadMethodCallException('This method requires a collection of Adherent entities');
            }

            fputcsv($handle, [
                $adherent->getFirstName(),
                $adherent->getLastName(),
                $adherent->getAge(),
                $adherent->getCityName(),
                $adherent->getCountry(),
                $adherent->getEmailAddress(),
            ]);
        }

        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        return $csv;
    }
}
