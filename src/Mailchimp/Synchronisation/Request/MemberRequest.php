<?php

namespace App\Mailchimp\Synchronisation\Request;

use App\Entity\Geo\Zone;

class MemberRequest implements MemberRequestInterface
{
    public const DATE_FORMAT = 'Y-m-d';

    public const MERGE_FIELD_FIRST_NAME = 'FNAME';
    public const MERGE_FIELD_LAST_NAME = 'LNAME';
    public const MERGE_FIELD_CITY = 'CITY';
    public const MERGE_FIELD_GENDER = 'GENDER';
    public const MERGE_FIELD_BIRTHDATE = 'BIRTHDATE';
    public const MERGE_FIELD_ZIP_CODE = 'ZIP_CODE';
    public const MERGE_FIELD_COUNTRY = 'COUNTRY';
    public const MERGE_FIELD_ADHESION_DATE = 'ADHESION';
    public const MERGE_FIELD_FAVORITE_CITIES = 'FVR_CITIES';
    public const MERGE_FIELD_FAVORITE_CITIES_CODES = 'FVR_CODES';
    public const MERGE_FIELD_MUNICIPAL_TEAM = 'MUNIC_TEAM';
    public const MERGE_FIELD_REFERENT_TAGS = 'REF_TAGS';
    public const MERGE_FIELD_INSEE_CODE = 'INSEE_CODE';
    public const MERGE_FIELD_DEPARTMENTAL_CODE = 'DPT_CODE';
    public const MERGE_FIELD_ADHERENT = 'ISADHERENT';
    public const MERGE_FIELD_ZONE_CITY = 'ZONE_CITY';
    public const MERGE_FIELD_ZONE_DEPARTMENT = 'ZONE_DPT';
    public const MERGE_FIELD_ZONE_REGION = 'ZONE_REGIO';
    public const MERGE_FIELD_ZONE_COUNTRY = 'ZONE_CNTRY';
    public const MERGE_FIELD_TEAM_CODE = 'TEAM_CODE';
    public const MERGE_FIELD_CODE_CANTON = 'CODE_CNTN';
    public const MERGE_FIELD_CODE_DEPARTMENT = 'CODE_DPT';
    public const MERGE_FIELD_CODE_REGION = 'CODE_REGIO';
    public const MERGE_FIELD_SOURCE = 'SOURCE';

    private $memberIdentifier;

    private $emailAddress;
    private $emailType = 'html'; // or 'text'
    private $status = 'subscribed';
    private $mergeFields = [];
    private $interests = [];

    public function __construct(string $memberIdentifier)
    {
        $this->memberIdentifier = $memberIdentifier;
    }

    public function getMemberIdentifier(): string
    {
        return $this->memberIdentifier;
    }

    public function setEmailAddress($emailAddress): void
    {
        $this->emailAddress = $emailAddress;
    }

    public function setUnsubscriptionRequest(): void
    {
        $this->status = 'unsubscribed';
    }

    public function setMergeFields(array $mergeFields): void
    {
        $this->mergeFields = $mergeFields;
    }

    public function setInterests(array $interests): void
    {
        $this->interests = $interests;
    }

    public function toArray(): array
    {
        $data = [
            'email_address' => $this->emailAddress ?? $this->memberIdentifier,
            'email_type' => $this->emailType,
            'status' => $this->status,
        ];

        if ($this->mergeFields) {
            $data['merge_fields'] = $this->mergeFields;
        }

        if ($this->interests) {
            $data['interests'] = $this->interests;
        }

        return $data;
    }

    public static function getMergeFieldFromZone(Zone $zone): string
    {
        switch ($zone->getType()) {
            case Zone::CITY:
                return self::MERGE_FIELD_ZONE_CITY;
            case Zone::DEPARTMENT:
                return self::MERGE_FIELD_ZONE_DEPARTMENT;
            case Zone::REGION:
                return self::MERGE_FIELD_ZONE_REGION;
            case Zone::COUNTRY:
                return self::MERGE_FIELD_ZONE_COUNTRY;
            default:
                throw new \InvalidArgumentException(sprintf('Zone type "%s" is not synchronized with mailchimp.', $zone->getType()));
        }
    }

    public static function getMergeCodeFieldFromZone(Zone $zone): string
    {
        switch ($zone->getType()) {
            case Zone::CANTON:
                return self::MERGE_FIELD_CODE_CANTON;
            case Zone::DEPARTMENT:
                return self::MERGE_FIELD_CODE_DEPARTMENT;
            case Zone::REGION:
                return self::MERGE_FIELD_CODE_REGION;
            default:
                throw new \InvalidArgumentException(sprintf('Zone code type "%s" is not synchronized with mailchimp.', $zone->getType()));
        }
    }
}
