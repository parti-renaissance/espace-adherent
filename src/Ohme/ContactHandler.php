<?php

namespace App\Ohme;

use App\Entity\Ohme\Contact;

class ContactHandler
{
    public function __construct(
        private readonly ClientInterface $client,
        private readonly PaymentImporter $paymentImporter
    ) {
    }

    public function updateAdherentLink(Contact $contact): void
    {
        $adherent = $contact->adherent;

        $this->client->updateContact($contact->ohmeIdentifier, [
            'uuid_adherent' => $adherent?->getUuid()->toString(),
        ]);

        if (!$adherent) {
            return;
        }

        $totalPayments = $this->paymentImporter->getPaymentsCount([], $contact);
        $pageSize = 100;
        $offset = 0;

        do {
            $this->paymentImporter->importPayments($pageSize, $offset, [], $contact);

            $offset += $pageSize;
        } while ($offset < $totalPayments);
    }
}
