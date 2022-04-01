<?php

namespace App\SendInBlue;

use App\Entity\Adherent;
use App\Utils\PhoneNumberUtils;

class AdherentManager
{
    private const FIELD_EMAIL = 'EMAIL';
    private const FIELD_SOURCE = 'SOURCE';
    private const FIELD_FIRST_NAME = 'PRENOM';
    private const FIELD_LAST_NAME = 'NOM';
    private const FIELD_BIRTHDATE = 'DATE_NAISSANCE';
    private const FIELD_PHONE = 'PHONE';
    private const FIELD_CITY = 'VILLE';
    private const FIELD_POSTAL_CODE = 'CODE_POSTAL';
    private const FIELD_COUNTRY = 'PAYS';

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
            $adherent->getEmailAddress(),
            $this->adherentListId,
            $this->createAttributes($adherent, $identifier)
        );
    }

    public function delete(Adherent $adherent): void
    {
        $this->client->delete($adherent->getEmailAddress());
    }

    private function createAttributes(Adherent $adherent, string $identifier): array
    {
        return [
            self::FIELD_EMAIL => $identifier,

            self::FIELD_SOURCE => $adherent->getSource(),
            self::FIELD_FIRST_NAME => $adherent->getFirstName(),
            self::FIELD_LAST_NAME => $adherent->getLastName(),

            self::FIELD_BIRTHDATE => $adherent->getBirthdate() ? $adherent->getBirthdate()->format('Y-m-d') : null,
            self::FIELD_PHONE => PhoneNumberUtils::format($adherent->getPhone()),

            self::FIELD_CITY => $adherent->getCityName(),
            self::FIELD_POSTAL_CODE => $adherent->getPostalCode(),
            self::FIELD_COUNTRY => $adherent->getCountry(),
        ];
    }
}
