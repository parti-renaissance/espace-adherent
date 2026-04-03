<?php

declare(strict_types=1);

namespace Tests\App\Validator;

use App\Adherent\MandateTypeEnum;
use App\Entity\Adherent;
use App\Entity\AdherentMandate\ElectedRepresentativeAdherentMandate;
use App\Entity\Geo\Zone;
use App\Validator\MandateZoneType;
use App\Validator\MandateZoneTypeValidator;
use PHPUnit\Framework\MockObject\MockObject;

class MandateZoneTypeValidatorTest extends ConstraintValidatorTestCase
{
    private const VIOLATION_MESSAGE = 'La zone sélectionnée n\'est pas valide pour ce type de mandat.';

    public function testNullZoneIsValid(): void
    {
        $mandate = $this->createMandate(MandateTypeEnum::MAIRE, null);

        $this->validator->validate($mandate, new MandateZoneType());

        $this->assertNoViolation();
    }

    public function testValidZoneTypeForMaire(): void
    {
        $zone = $this->createZoneMock(Zone::CITY, '75056');
        $mandate = $this->createMandate(MandateTypeEnum::MAIRE, $zone);

        $this->validator->validate($mandate, new MandateZoneType());

        $this->assertNoViolation();
    }

    public function testValidZoneTypeForMaireWithBorough(): void
    {
        $zone = $this->createZoneMock(Zone::BOROUGH, '75101');
        $mandate = $this->createMandate(MandateTypeEnum::MAIRE, $zone);

        $this->validator->validate($mandate, new MandateZoneType());

        $this->assertNoViolation();
    }

    public function testInvalidZoneTypeForMaire(): void
    {
        $zone = $this->createZoneMock(Zone::DEPARTMENT, '75');
        $mandate = $this->createMandate(MandateTypeEnum::MAIRE, $zone);

        $this->validator->validate($mandate, new MandateZoneType());

        $this
            ->buildViolation(self::VIOLATION_MESSAGE)
            ->atPath('property.path.zone')
            ->assertRaised()
        ;
    }

    public function testValidZoneTypeForDepute(): void
    {
        $zone = $this->createZoneMock(Zone::DISTRICT, '75-01');
        $mandate = $this->createMandate(MandateTypeEnum::DEPUTE, $zone);

        $this->validator->validate($mandate, new MandateZoneType());

        $this->assertNoViolation();
    }

    public function testInvalidZoneTypeForDepute(): void
    {
        $zone = $this->createZoneMock(Zone::CITY, '75056');
        $mandate = $this->createMandate(MandateTypeEnum::DEPUTE, $zone);

        $this->validator->validate($mandate, new MandateZoneType());

        $this
            ->buildViolation(self::VIOLATION_MESSAGE)
            ->atPath('property.path.zone')
            ->assertRaised()
        ;
    }

    public function testValidZoneTypeAndCodeForMinister(): void
    {
        $zone = $this->createZoneMock(Zone::COUNTRY, 'FR');
        $mandate = $this->createMandate(MandateTypeEnum::MINISTER, $zone);

        $this->validator->validate($mandate, new MandateZoneType());

        $this->assertNoViolation();
    }

    public function testInvalidZoneCodeForMinister(): void
    {
        $zone = $this->createZoneMock(Zone::COUNTRY, 'DE');
        $mandate = $this->createMandate(MandateTypeEnum::MINISTER, $zone);

        $this->validator->validate($mandate, new MandateZoneType());

        $this
            ->buildViolation(self::VIOLATION_MESSAGE)
            ->atPath('property.path.zone')
            ->assertRaised()
        ;
    }

    public function testInvalidZoneTypeForMinister(): void
    {
        $zone = $this->createZoneMock(Zone::CITY, '75056');
        $mandate = $this->createMandate(MandateTypeEnum::MINISTER, $zone);

        $this->validator->validate($mandate, new MandateZoneType());

        $this
            ->buildViolation(self::VIOLATION_MESSAGE)
            ->atPath('property.path.zone')
            ->assertRaised()
        ;
    }

    public function testValidZoneTypeForConseillerTerritorial(): void
    {
        $zone = $this->createZoneMock(Zone::REGION, '94');
        $mandate = $this->createMandate(MandateTypeEnum::CONSEILLER_TERRITORIAL, $zone);

        $this->validator->validate($mandate, new MandateZoneType());

        $this->assertNoViolation();
    }

    public function testInvalidZoneCodeForConseillerTerritorial(): void
    {
        $zone = $this->createZoneMock(Zone::REGION, '75');
        $mandate = $this->createMandate(MandateTypeEnum::CONSEILLER_TERRITORIAL, $zone);

        $this->validator->validate($mandate, new MandateZoneType());

        $this
            ->buildViolation(self::VIOLATION_MESSAGE)
            ->atPath('property.path.zone')
            ->assertRaised()
        ;
    }

    protected function createValidator(): MandateZoneTypeValidator
    {
        return new MandateZoneTypeValidator();
    }

    private function createMandate(string $mandateType, ?Zone $zone): ElectedRepresentativeAdherentMandate
    {
        $adherent = $this->createMock(Adherent::class);

        return ElectedRepresentativeAdherentMandate::create(
            null,
            $adherent,
            $mandateType,
            new \DateTime(),
            null,
            null,
            $zone
        );
    }

    private function createZoneMock(string $type, string $code): Zone&MockObject
    {
        $zone = $this->createMock(Zone::class);
        $zone->method('getType')->willReturn($type);
        $zone->method('getCode')->willReturn($code);

        return $zone;
    }
}
