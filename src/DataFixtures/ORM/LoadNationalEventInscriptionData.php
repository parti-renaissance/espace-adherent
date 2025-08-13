<?php

namespace App\DataFixtures\ORM;

use App\Entity\Adherent;
use App\Entity\NationalEvent\EventInscription;
use App\Entity\NationalEvent\NationalEvent;
use App\Entity\NationalEvent\Payment;
use App\Entity\NationalEvent\PaymentStatus;
use App\NationalEvent\InscriptionStatusEnum;
use App\NationalEvent\PaymentStatusEnum;
use App\ValueObject\Genders;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;
use Ramsey\Uuid\Uuid;

class LoadNationalEventInscriptionData extends Fixture implements DependentFixtureInterface
{
    private Generator $faker;

    public function __construct()
    {
        $this->faker = Factory::create('fr_FR');
    }

    public function load(ObjectManager $manager): void
    {
        /** @var NationalEvent $event */
        $event = $this->getReference('event-national-1', NationalEvent::class);
        $zone92 = LoadGeoZoneData::getZoneReference($manager, 'zone_department_92');
        $adherent = $this->getReference('adherent-4', Adherent::class);

        for ($i = 1; $i <= 100; ++$i) {
            $manager->persist($eventInscription = new EventInscription($event));
            $eventInscription->firstName = $this->faker->firstName();
            $eventInscription->lastName = $this->faker->lastName();
            $eventInscription->gender = 0 === $i % 2 ? Genders::FEMALE : Genders::MALE;
            $eventInscription->ticketQRCodeFile = 0 === $i % 2 ? $eventInscription->getUuid()->toString().'.png' : null;
            $eventInscription->ticketSentAt = 0 === $i % 2 ? new \DateTime() : null;
            $eventInscription->addressEmail = $this->faker->email();
            $eventInscription->postalCode = '92110';
            $eventInscription->addZone($zone92);
            $eventInscription->birthdate = $this->faker->dateTimeBetween('-100 years', '-15 years');
            $eventInscription->status = 0 === $i % 10 ? InscriptionStatusEnum::INCONCLUSIVE : InscriptionStatusEnum::ACCEPTED;
        }

        /** @var NationalEvent $event */
        $event = $this->getReference('event-national-3', NationalEvent::class);

        for ($i = 1; $i <= 5; ++$i) {
            $manager->persist($eventInscription = new EventInscription($event));
            $eventInscription->firstName = $this->faker->firstName();
            $eventInscription->lastName = $this->faker->lastName();
            $eventInscription->gender = 0 === $i % 2 ? Genders::FEMALE : Genders::MALE;
            $eventInscription->ticketQRCodeFile = 0 === $i % 2 ? $eventInscription->getUuid()->toString().'.png' : null;
            $eventInscription->ticketSentAt = 0 === $i % 2 ? new \DateTime() : null;
            $eventInscription->addressEmail = $this->faker->email();
            $eventInscription->postalCode = '92110';
            $eventInscription->birthdate = $this->faker->dateTimeBetween('-100 years', '-15 years');
            $eventInscription->status = InscriptionStatusEnum::WAITING_PAYMENT;
            $eventInscription->isJAM = 0 === $i % 2;
            $eventInscription->volunteer = 0 === $i % 2;
            $eventInscription->accessibility = 4 === $i ? null : 'handicap_moteur';
            $eventInscription->amount = 5000;
            $eventInscription->visitDay = 'dimanche';
            $eventInscription->transport = 'dimanche_train';
            $eventInscription->addZone($zone92);

            $eventInscription->addPayment($payment = new Payment(
                $uuid = Uuid::uuid4(),
                $eventInscription,
                $eventInscription->amount,
                $eventInscription->visitDay,
                $eventInscription->transport,
                $eventInscription->accommodation,
                $eventInscription->withDiscount,
                ['orderID' => $uuid->toString()]
            ));

            if (0 === $i % 2) {
                $eventInscription->addPayment($payment = new Payment(
                    $uuid = Uuid::uuid4(),
                    $eventInscription,
                    $eventInscription->amount,
                    $eventInscription->visitDay,
                    $eventInscription->transport,
                    $eventInscription->accommodation,
                    $eventInscription->withDiscount,
                    ['orderID' => $uuid->toString()]
                ));
                $payment->addStatus(new PaymentStatus($payment, ['orderID' => $uuid->toString(), 'STATUS' => 9, 'AMOUNT' => $eventInscription->amount]));
                $eventInscription->status = InscriptionStatusEnum::PENDING;
                $eventInscription->paymentStatus = PaymentStatusEnum::CONFIRMED;
            } else {
                $payment->addStatus(new PaymentStatus($payment, ['orderID' => $uuid->toString(), 'STATUS' => 5, 'AMOUNT' => $eventInscription->amount]));
            }
        }

        $eventInscription->adherent = $adherent;

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            LoadGeoZoneData::class,
            LoadAdherentData::class,
        ];
    }
}
