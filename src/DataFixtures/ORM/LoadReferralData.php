<?php

namespace App\DataFixtures\ORM;

use App\Adherent\Referral\ModeEnum;
use App\Adherent\Referral\StatusEnum;
use App\Adherent\Referral\TypeEnum;
use App\Entity\Adherent;
use App\Entity\Referral;
use App\Enum\CivilityEnum;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class LoadReferralData extends AbstractLoadPostAddressData implements DependentFixtureInterface
{
    public const UUID_1 = 'abeb6804-a88b-478a-8859-0c5e2f549d17';
    public const UUID_2 = '2055b072-73f4-46c3-a9ab-1fb617c464f1';
    public const UUID_3 = '34abd1e0-46e3-4c02-a4ad-8f632e03f7ce';
    public const UUID_4 = 'e12d55f2-2a27-49c9-92e5-818320f99749';
    public const UUID_5 = '680a34aa-8f03-4efc-a294-8e6c2bb669ab';
    public const UUID_6 = '748e94b8-5316-4885-9f42-99b8aa037efa';

    public function load(ObjectManager $manager): void
    {
        $manager->persist($this->createReferral(
            Uuid::fromString(self::UUID_1),
            'jean.martin@dev.test',
            'Jean',
            $this->getReference('adherent-1', Adherent::class),
            'PAB123'
        ));

        $manager->persist($referral = $this->createReferral(
            Uuid::fromString(self::UUID_2),
            'john.doe@dev.test',
            'John',
            $this->getReference('adherent-1', Adherent::class),
            'P789YZ'
        ));
        $referral->firstName = 'John';
        $referral->lastName = 'Doe';
        $referral->civility = CivilityEnum::Monsieur;
        $referral->nationality = 'FR';
        $referral->birthdate = new \DateTimeImmutable('1990-01-01');
        $referral->type = TypeEnum::PREREGISTRATION;
        $referral->setPostAddress($this->createNullablePostAddress('68 rue du Rocher', '75008-75108'));

        $manager->persist($this->createReferral(
            Uuid::fromString(self::UUID_3),
            'jane.doe@dev.test',
            'Jane',
            $this->getReference('adherent-1', Adherent::class),
            'PCD678',
            StatusEnum::REPORTED
        ));

        $manager->persist($this->createReferral(
            Uuid::fromString(self::UUID_4),
            'jean.dupont@dev.test',
            'Jean',
            $this->getReference('adherent-3', Adherent::class),
            'PAC123',
            StatusEnum::ADHESION_FINISHED
        ));

        $manager->persist($this->createReferral(
            Uuid::fromString(self::UUID_5),
            'jane.dupont@dev.test',
            'Jane',
            $this->getReference('adherent-4', Adherent::class),
            'PAC124',
            StatusEnum::ADHESION_FINISHED
        ));

        $manager->persist($this->createReferral(
            Uuid::fromString(self::UUID_6),
            'didier.dupont@dev.test',
            'Didier',
            $this->getReference('adherent-4', Adherent::class),
            'PAC125',
            StatusEnum::ADHESION_FINISHED
        ));

        $manager->flush();
    }

    private function createReferral(
        UuidInterface $uuid,
        string $emailAddress,
        string $firstName,
        Adherent $referrer,
        string $identifier,
        StatusEnum $status = StatusEnum::INVITATION_SENT,
    ): Referral {
        $referral = new Referral($emailAddress, $uuid);

        $referral->firstName = $firstName;
        $referral->referrer = $referrer;

        $referral->identifier = $identifier;
        $referral->type = TypeEnum::INVITATION;
        $referral->mode = ModeEnum::EMAIL;
        $referral->status = $status;

        return $referral;
    }

    public function getDependencies(): array
    {
        return [
            LoadAdherentData::class,
        ];
    }
}
