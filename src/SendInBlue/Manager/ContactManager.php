<?php

namespace App\SendInBlue\Manager;

use App\Entity\Contact;
use App\Membership\Contact\InterestEnum;
use App\SendInBlue\AttributesEnum;
use App\SendInBlue\ContactInterface;
use App\SendInBlue\Exception\UnexpectedTypeException;

class ContactManager extends AbstractManager
{
    public function supports(ContactInterface $contact): bool
    {
        return $contact instanceof Contact;
    }

    public function getIdentifier(ContactInterface $contact): string
    {
        if (!$contact instanceof Contact) {
            throw new UnexpectedTypeException($contact, Contact::class);
        }

        return $contact->getEmailAddress();
    }

    public function getAttributes(ContactInterface $contact): array
    {
        if (!$contact instanceof Contact) {
            throw new UnexpectedTypeException($contact, Contact::class);
        }

        return [
            AttributesEnum::FIELD_EMAIL => $contact->getEmailAddress(),

            AttributesEnum::FIELD_FIRST_NAME => $contact->getFirstName(),
            AttributesEnum::FIELD_LAST_NAME => $contact->getLastName(),

            AttributesEnum::FIELD_BIRTHDATE => self::formatDate($contact->getBirthdate()),
            AttributesEnum::FIELD_PHONE => self::formatPhone($contact->getPhone()),

            AttributesEnum::FIELD_CITY => $contact->getCityName(),
            AttributesEnum::FIELD_POSTAL_CODE => $contact->getPostalCode(),
            AttributesEnum::FIELD_COUNTRY => $contact->getCountry(),

            AttributesEnum::FIELD_SOURCE => $contact->getSource(),
            AttributesEnum::FIELD_CREATED_AT => self::formatDate($contact->getCreatedAt()),
            AttributesEnum::FIELD_UPDATED_AT => self::formatDate($contact->getUpdatedAt()),

            AttributesEnum::FIELD_ACTION_TERRAIN => \in_array(InterestEnum::ACTION_TERRAIN, $contact->getInterests(), true),
            AttributesEnum::FIELD_CAMPAGNE_NUMERIQUE => \in_array(InterestEnum::CAMPAGNE_NUMERIQUE, $contact->getInterests(), true),
            AttributesEnum::FIELD_PROCHES => \in_array(InterestEnum::PROCHES, $contact->getInterests(), true),
            AttributesEnum::FIELD_INTERESTS_UPDATED_AT => $contact->getInterestsUpdatedAt(),

            AttributesEnum::FIELD_MAIL_CONTACT => $contact->isMailContact(),
            AttributesEnum::FIELD_PHONE_CONTACT => $contact->isPhoneContact(),
        ];
    }
}
