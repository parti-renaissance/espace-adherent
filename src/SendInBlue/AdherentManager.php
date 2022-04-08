<?php

namespace App\SendInBlue;

use App\Entity\Adherent;
use App\Utils\PhoneNumberUtils;

class AdherentManager
{
    private const FIELD_STATUS = 'STATUS';
    private const FIELD_CREATED_AT = 'CREATED_AT';
    private const FIELD_UPDATED_AT = 'UPDATED_AT';
    private const FIELD_ACTIVATED_AT = 'ACTIVATED_AT';
    private const FIELD_EMAIL = 'EMAIL';
    private const FIELD_SOURCE = 'SOURCE';
    private const FIELD_FIRST_NAME = 'PRENOM';
    private const FIELD_LAST_NAME = 'NOM';
    private const FIELD_BIRTHDATE = 'DATE_NAISSANCE';
    private const FIELD_PHONE = 'PHONE';
    private const FIELD_CITY = 'VILLE';
    private const FIELD_POSTAL_CODE = 'CODE_POSTAL';
    private const FIELD_COUNTRY = 'PAYS';
    private const FIELD_SUBSCRIPTION_TYPES = 'SUBSCRIPTION_TYPES';

    private ClientInterface $client;
    private int $adherentListId;

    public function __construct(ClientInterface $client, int $sendInBlueAdherentListId)
    {
        $this->client = $client;
        $this->adherentListId = $sendInBlueAdherentListId;
    }

    public function synchronize(Adherent $adherent, string $identifier): void
    {
        $this->client->synchronize(
            $identifier,
            $this->adherentListId,
            $this->createAttributes($adherent)
        );
    }

    public function delete(Adherent $adherent): void
    {
        $this->client->delete($adherent->getEmailAddress());
    }

    private function createAttributes(Adherent $adherent): array
    {
        return [
            self::FIELD_EMAIL => $adherent->getEmailAddress(),

            self::FIELD_SOURCE => $adherent->getSource(),
            self::FIELD_FIRST_NAME => $adherent->getFirstName(),
            self::FIELD_LAST_NAME => $adherent->getLastName(),

            self::FIELD_BIRTHDATE => $adherent->getBirthdate() ? $adherent->getBirthdate()->format('Y-m-d') : null,
            self::FIELD_PHONE => PhoneNumberUtils::format($adherent->getPhone()),

            self::FIELD_CITY => $adherent->getCityName(),
            self::FIELD_POSTAL_CODE => $adherent->getPostalCode(),
            self::FIELD_COUNTRY => $adherent->getCountry(),

            self::FIELD_STATUS => $adherent->getStatus(),
            self::FIELD_CREATED_AT => $adherent->getRegisteredAt() ? $adherent->getRegisteredAt()->format('Y-m-d') : null,
            self::FIELD_UPDATED_AT => $adherent->getUpdatedAt() ? $adherent->getUpdatedAt()->format('Y-m-d') : null,
            self::FIELD_ACTIVATED_AT => $adherent->getActivatedAt() ? $adherent->getActivatedAt()->format('Y-m-d') : null,

            self::FIELD_SUBSCRIPTION_TYPES => implode(',', $adherent->getSubscriptionTypeCodes()),
        ];
    }
}
