<?php

declare(strict_types=1);

namespace App\Ohme;

use App\Entity\Ohme\Contact;
use App\Repository\AdherentRepository;
use App\Repository\Ohme\ContactRepository;
use Doctrine\ORM\EntityManagerInterface;

class ContactImporter
{
    public function __construct(
        private readonly ClientInterface $client,
        private readonly ContactRepository $contactRepository,
        private readonly AdherentRepository $adherentRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function getContactsCount(array $options = []): int
    {
        $contacts = $this->client->getContacts(1, 0, $options);

        return $contacts['count'] ?? 0;
    }

    public function importContacts(int $limit = 100, int $offset = 0, array $options = []): int
    {
        $contacts = $this->client->getContacts($limit, $offset, $options);

        if (empty($contacts['data']) || !is_iterable($contacts['data'])) {
            return 0;
        }

        $total = 0;

        foreach ($contacts['data'] as $contactData) {
            ++$total;

            if (empty($contactData['id'])) {
                continue;
            }

            $identifier = (string) $contactData['id'];

            $contact = $this->findContact($identifier) ?? $this->createContact($identifier);

            $this->updateContact($contact, $contactData);
        }

        $this->entityManager->clear();

        return $total;
    }

    private function findContact(string $ohmeIdentifier): ?Contact
    {
        return $this->contactRepository->findOneByOhmeIdentifier($ohmeIdentifier);
    }

    private function createContact(string $ohmeIdentifier): Contact
    {
        $contact = new Contact();
        $contact->ohmeIdentifier = $ohmeIdentifier;

        return $contact;
    }

    private function updateContact(Contact $contact, array $data): void
    {
        if (!empty($data['email'])) {
            $contact->email = $data['email'];
        }

        if (!empty($data['firstname'])) {
            $contact->firstname = $data['firstname'];
        }

        if (!empty($data['lastname'])) {
            $contact->lastname = $data['lastname'];
        }

        if (!empty($data['civility'])) {
            $contact->civility = $data['civility'];
        }

        if (!empty($data['email'])) {
            $contact->email = $data['email'];
        }

        if (!empty($data['birthdate'])) {
            $contact->birthdate = new \DateTimeImmutable($data['birthdate']);
        }

        if (!empty($data['address'])) {
            $address = $data['address'];

            if (!empty($address['street'])) {
                $contact->addressStreet = $address['street'];
            }

            if (!empty($address['street_2'])) {
                $contact->addressStreet2 = $address['street_2'];
            }

            if (!empty($address['city'])) {
                $contact->addressCity = $address['city'];
            }

            if (!empty($address['post_code'])) {
                $contact->addressPostCode = $address['post_code'];
            }

            if (!empty($address['country'])) {
                $contact->addressCountry = $address['country'];
            }

            if (!empty($address['country_code'])) {
                $contact->addressCountryCode = $address['country_code'];
            }
        }

        if (!empty($data['phone'])) {
            $contact->phone = $data['phone'];
        }

        if (!empty($data['created_at'])) {
            $contact->ohmeCreatedAt = new \DateTimeImmutable($data['created_at']);
        }

        if (!empty($data['updated_at'])) {
            $contact->ohmeUpdatedAt = new \DateTimeImmutable($data['updated_at']);
        }

        if (
            !empty($data['uuid_adherent'])
            && (
                !$contact->adherent
                || $contact->adherent->getUuid()->toString() !== $data['uuid_adherent']
            )
        ) {
            $contact->adherent = $this->adherentRepository->findOneByUuid($data['uuid_adherent']);
        }

        $this->contactRepository->save($contact);
    }
}
