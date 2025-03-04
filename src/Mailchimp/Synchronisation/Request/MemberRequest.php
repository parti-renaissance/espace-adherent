<?php

namespace App\Mailchimp\Synchronisation\Request;

use App\Entity\Geo\Zone;
use App\Mailchimp\Exception\ZoneNotSynchronizedException;

class MemberRequest implements MemberRequestInterface
{
    public const DATE_FORMAT = 'Y-m-d';

    public const MERGE_FIELD_PUBLIC_ID = 'PUBLIC_ID';
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
    public const MERGE_FIELD_INSEE_CODE = 'INSEE_CODE';
    public const MERGE_FIELD_DEPARTMENTAL_CODE = 'DPT_CODE';
    public const MERGE_FIELD_ADHERENT = 'ISADHERENT';
    public const MERGE_FIELD_FIRST_MEMBERSHIP_DONATION = 'FIRSTCOTIS';
    public const MERGE_FIELD_LAST_MEMBERSHIP_DONATION = 'COTISATION';
    public const MERGE_FIELD_ZONE_BOROUGH = 'ZONE_BRGH';
    public const MERGE_FIELD_ZONE_CITY = 'ZONE_CITY';
    public const MERGE_FIELD_ZONE_DISTRICT = 'ZONE_DIST';
    public const MERGE_FIELD_ZONE_FOREIGN_DISTRICT = 'ZONE_FDIST';
    public const MERGE_FIELD_ZONE_CANTON = 'ZONE_CNTN';
    public const MERGE_FIELD_ZONE_DEPARTMENT = 'ZONE_DPT';
    public const MERGE_FIELD_ZONE_REGION = 'ZONE_REGIO';
    public const MERGE_FIELD_ZONE_COUNTRY = 'ZONE_CNTRY';
    public const MERGE_FIELD_TEAM_CODE = 'TEAM_CODE';
    public const MERGE_FIELD_CODE_CANTON = 'CODE_CNTN';
    public const MERGE_FIELD_CODE_DEPARTMENT = 'CODE_DPT';
    public const MERGE_FIELD_CODE_REGION = 'CODE_REGIO';
    public const MERGE_FIELD_ZONE = 'ZONE';
    public const MERGE_FIELD_ZONE_CODES = 'ZONE_CODES';
    public const MERGE_FIELD_SOURCE = 'SOURCE';
    public const MERGE_FIELD_ADHERENT_TAGS = 'ADRNT_TAGS';
    public const MERGE_FIELD_CERTIFIED = 'CERTIFIED';
    public const MERGE_FIELD_LAST_LOGIN_GROUP = 'LOGIN_GRP';
    public const MERGE_FIELD_COMMITTEE = 'COMMITTEE';
    public const MERGE_FIELD_MANDATE_TYPES = 'TYP_MANDAT';
    public const MERGE_FIELD_DECLARED_MANDATES = 'DEC_MANDAT';
    public const MERGE_FIELD_CAMPUS_REGISTRATION_DATE = 'CAMPUS_REG';
    public const MERGE_FIELD_DONATION_YEARS = 'DON_YEARS';

    public const ZONE_MERGE_FIELD_BY_ZONE_TYPE = [
        self::MERGE_FIELD_ZONE_BOROUGH => Zone::BOROUGH,
        self::MERGE_FIELD_ZONE_CITY => Zone::CITY,
        self::MERGE_FIELD_ZONE_DISTRICT => Zone::DISTRICT,
        self::MERGE_FIELD_ZONE_FOREIGN_DISTRICT => Zone::FOREIGN_DISTRICT,
        self::MERGE_FIELD_ZONE_CANTON => Zone::CANTON,
        self::MERGE_FIELD_ZONE_DEPARTMENT => Zone::DEPARTMENT,
        self::MERGE_FIELD_ZONE_REGION => Zone::REGION,
        self::MERGE_FIELD_ZONE_COUNTRY => Zone::COUNTRY,
    ];

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
            case Zone::BOROUGH:
                return self::MERGE_FIELD_ZONE_BOROUGH;
            case Zone::CITY:
                return self::MERGE_FIELD_ZONE_CITY;
            case Zone::CANTON:
                return self::MERGE_FIELD_ZONE_CANTON;
            case Zone::DEPARTMENT:
                return self::MERGE_FIELD_ZONE_DEPARTMENT;
            case Zone::REGION:
                return self::MERGE_FIELD_ZONE_REGION;
            case Zone::COUNTRY:
                return self::MERGE_FIELD_ZONE_COUNTRY;
            case Zone::DISTRICT:
                return self::MERGE_FIELD_ZONE_DISTRICT;
            case Zone::FOREIGN_DISTRICT:
                return self::MERGE_FIELD_ZONE_FOREIGN_DISTRICT;
            default:
                throw new ZoneNotSynchronizedException($zone);
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
                throw new \InvalidArgumentException(\sprintf('Zone code type "%s" is not synchronized with mailchimp.', $zone->getType()));
        }
    }
}
