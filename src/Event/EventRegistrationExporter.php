<?php

namespace App\Event;

use App\Entity\EventRegistration;
use App\Exception\EventException;

class EventRegistrationExporter
{
    public function export(array $registrations): string
    {
        $handle = fopen('php://memory', 'r+');
        fputs($handle, \chr(0xEF).\chr(0xBB).\chr(0xBF)); // add BOM to fix UTF-8 in Excel
        fputcsv($handle, ['N° d\'enregistrement', 'Prénom', 'Nom', 'Date d\'inscription']);

        foreach ($registrations as $registration) {
            if (!$registration instanceof EventRegistration) {
                throw new EventException('Invalid registration given');
            }

            fputcsv($handle, [
                $registration->getUuid()->toString(),
                $registration->getFirstName(),
                $registration->getLastName(),
                $registration->getCreatedAt()->format('d/m/Y à H:i'),
            ]);
        }

        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        return $csv;
    }
}
