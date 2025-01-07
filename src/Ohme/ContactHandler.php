<?php

namespace App\Ohme;

use App\Entity\Ohme\Contact;

class ContactHandler
{
    public function __construct(
        private readonly ClientInterface $client,
        private readonly PaymentImporter $paymentImporter,
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

        $this->paymentImporter->importPayments($contact);
    }
}
