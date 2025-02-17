<?php

namespace App\DataFixtures\ORM;

use App\Adherent\Referral\IdentifierGenerator;
use App\Adherent\Referral\ModeEnum;
use App\Adherent\Referral\StatusEnum;
use App\Adherent\Referral\TypeEnum;
use App\Entity\Adherent;
use App\Entity\Referral;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class LoadReferralData extends Fixture implements DependentFixtureInterface
{
    public const UUID_1 = 'abeb6804-a88b-478a-8859-0c5e2f549d17';
    public const UUID_2 = '2055b072-73f4-46c3-a9ab-1fb617c464f1';

    public function __construct(private readonly IdentifierGenerator $referralIdentifierGenerator)
    {
    }

    public function load(ObjectManager $manager): void
    {
        $manager->persist($this->createReferral(
            Uuid::fromString(self::UUID_1),
            'jean.martin@dev.test',
            'Jean',
            $this->getReference('adherent-1', Adherent::class)
        ));

        $manager->persist($this->createReferral(
            Uuid::fromString(self::UUID_2),
            'john.doe@dev.test',
            'John',
            $this->getReference('adherent-1', Adherent::class)
        ));

        $manager->flush();
    }

    private function createReferral(
        UuidInterface $uuid,
        string $emailAddress,
        string $firstName,
        ?Adherent $referrer = null,
    ): Referral {
        $referral = new Referral($uuid);

        $referral->emailAddress = $emailAddress;
        $referral->firstName = $firstName;
        $referral->referrer = $referrer;

        $referral->identifier = $this->referralIdentifierGenerator->generate();
        $referral->type = TypeEnum::INVITATION;
        $referral->mode = ModeEnum::EMAIL;
        $referral->status = StatusEnum::INVITATION_SENT;

        return $referral;
    }

    public function getDependencies(): array
    {
        return [
            LoadAdherentData::class,
        ];
    }
}
