<?php

namespace App\SendInBlue\Manager;

use App\Entity\Adherent;
use App\SendInBlue\AttributesEnum;
use App\SendInBlue\ContactInterface;
use App\SendInBlue\Exception\UnexpectedTypeException;

class AdherentManager extends AbstractManager
{
    public function supports(ContactInterface $contact): bool
    {
        return $contact instanceof Adherent;
    }

    public function getIdentifier(ContactInterface $contact): string
    {
        if (!$contact instanceof Adherent) {
            throw new UnexpectedTypeException($contact, Adherent::class);
        }

        return $contact->getEmailAddress();
    }

    public function getAttributes(ContactInterface $contact): array
    {
        if (!$contact instanceof Adherent) {
            throw new UnexpectedTypeException($contact, Adherent::class);
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
            AttributesEnum::FIELD_STATUS => $contact->getStatus(),
            AttributesEnum::FIELD_CREATED_AT => self::formatDate($contact->getRegisteredAt()),
            AttributesEnum::FIELD_UPDATED_AT => self::formatDate($contact->getUpdatedAt()),
            AttributesEnum::FIELD_ACTIVATED_AT => self::formatDate($contact->getActivatedAt()),

            AttributesEnum::FIELD_SUBSCRIPTION_TYPES => implode(',', $contact->getSubscriptionTypeCodes()),
        ];
    }
}
