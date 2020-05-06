<?php

namespace App\CitizenAction;

use Symfony\Component\OptionsResolver\OptionsResolver;

class CitizenActionParticipantsExporter
{
    public function export(array $participants): string
    {
        $handle = fopen('php://memory', 'r+');
        fputs($handle, \chr(0xEF).\chr(0xBB).\chr(0xBF)); // add BOM to fix UTF-8 in Excel
        fputcsv($handle, ['N° d\'enregistrement', 'Prénom', 'Nom', 'Âge', 'Ville', 'Date d\'inscription']);

        foreach ($participants as $participant) {
            $resolver = new OptionsResolver();
            $resolver->setDefined(['lastNameInitial', 'postalCode', 'administrator']);
            $resolver->setRequired(['uuid', 'firstName', 'lastName', 'age', 'cityName', 'createdAt']);
            $resolver->setAllowedTypes('createdAt', \DateTime::class);
            $resolver->resolve($participant);

            fputcsv($handle, [
                $participant['uuid'],
                $participant['firstName'],
                strtoupper($participant['lastName']),
                $participant['age'],
                $participant['cityName'],
                $participant['createdAt']->format('d/m/Y à H:i'),
            ]);
        }

        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        return $csv;
    }
}
