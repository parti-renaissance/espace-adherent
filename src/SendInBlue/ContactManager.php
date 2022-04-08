<?php

namespace App\SendInBlue;

use App\Entity\Contact;
use App\Membership\Contact\InterestEnum;
use App\Repository\AdherentRepository;
use App\Utils\PhoneNumberUtils;

class ContactManager
{
    private const FIELD_SOURCE = 'SOURCE';
    private const FIELD_FIRST_NAME = 'PRENOM';
    private const FIELD_LAST_NAME = 'NOM';
    private const FIELD_ACTION_TERRAIN = 'ACTION_TERRAIN';
    private const FIELD_CAMPAGNE_NUMERIQUE = 'CAMPAGNE_NUMERIQUE';
    private const FIELD_PROCHES = 'CONVAINCRE_PROCHE';
    private const FIELD_INTERESTS_UPDATED_AT = 'INTERESTS_UPDATED_AT';
    private const FIELD_BIRTHDATE = 'DATE_NAISSANCE';
    private const FIELD_PHONE = 'PHONE';
    private const FIELD_MAIL_CONTACT = 'MAIL_CONTACT';
    private const FIELD_PHONE_CONTACT = 'PHONE_CONTACT';
    private const FIELD_CITY = 'VILLE';
    private const FIELD_POSTAL_CODE = 'CODE_POSTAL';
    private const FIELD_COUNTRY = 'PAYS';

    private ClientInterface $client;
    private int $contactListId;
    private AdherentRepository $adherentRepository;

    public function __construct(
        ClientInterface $client,
        int $sendInBlueContactListId,
        AdherentRepository $adherentRepository
    ) {
        $this->client = $client;
        $this->contactListId = $sendInBlueContactListId;
        $this->adherentRepository = $adherentRepository;
    }

    public function synchronize(Contact $contact): void
    {
        if ($this->adherentExists($contact->getEmailAddress())) {
            return;
        }

        $this->client->synchronize(
            $contact->getEmailAddress(),
            $this->contactListId,
            $this->createAttributes($contact)
        );
    }

    private function adherentExists(string $email): bool
    {
        return null !== $this->adherentRepository->findOneByEmail($email);
    }

    private function createAttributes(Contact $contact): array
    {
        return [
            self::FIELD_SOURCE => $contact->getSource(),
            self::FIELD_FIRST_NAME => $contact->getFirstName(),
            self::FIELD_LAST_NAME => $contact->getLastName(),
            self::FIELD_ACTION_TERRAIN => \in_array(InterestEnum::ACTION_TERRAIN, $contact->getInterests(), true),
            self::FIELD_CAMPAGNE_NUMERIQUE => \in_array(InterestEnum::CAMPAGNE_NUMERIQUE, $contact->getInterests(), true),
            self::FIELD_PROCHES => \in_array(InterestEnum::PROCHES, $contact->getInterests(), true),
            self::FIELD_INTERESTS_UPDATED_AT => $contact->getInterestsUpdatedAt(),
            self::FIELD_BIRTHDATE => $contact->getBirthdate() ? $contact->getBirthdate()->format('Y-m-d') : null,
            self::FIELD_PHONE => PhoneNumberUtils::format($contact->getPhone()),
            self::FIELD_MAIL_CONTACT => $contact->isMailContact(),
            self::FIELD_PHONE_CONTACT => $contact->isPhoneContact(),
            self::FIELD_CITY => $contact->getCityName(),
            self::FIELD_POSTAL_CODE => $contact->getPostalCode(),
            self::FIELD_COUNTRY => $contact->getCountry(),
        ];
    }
}
