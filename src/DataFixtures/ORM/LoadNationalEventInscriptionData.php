<?php

namespace App\DataFixtures\ORM;

use App\Entity\NationalEvent\EventInscription;
use App\Entity\NationalEvent\NationalEvent;
use App\Entity\NationalEvent\Payment;
use App\Entity\NationalEvent\PaymentStatus;
use App\NationalEvent\InscriptionStatusEnum;
use App\ValueObject\Genders;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;
use Ramsey\Uuid\Uuid;

class LoadNationalEventInscriptionData extends Fixture
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

        for ($i = 1; $i <= 100; ++$i) {
            $manager->persist($eventInscription = new EventInscription($event));
            $eventInscription->firstName = $this->faker->firstName();
            $eventInscription->lastName = $this->faker->lastName();
            $eventInscription->gender = 0 === $i % 2 ? Genders::FEMALE : Genders::MALE;
            $eventInscription->ticketQRCodeFile = 0 === $i % 2 ? $eventInscription->getUuid()->toString().'.png' : null;
            $eventInscription->ticketSentAt = 0 === $i % 2 ? new \DateTime() : null;
            $eventInscription->addressEmail = $this->faker->email();
            $eventInscription->postalCode = $this->faker->postcode();
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
            $eventInscription->postalCode = $this->faker->postcode();
            $eventInscription->birthdate = $this->faker->dateTimeBetween('-100 years', '-15 years');
            $eventInscription->status = InscriptionStatusEnum::WAITING_PAYMENT;
            $eventInscription->isJAM = 0 === $i % 2;
            $eventInscription->volunteer = 0 === $i % 2;
            $eventInscription->accessibility = 4 === $i ? null : 'handicap_moteur';
            $eventInscription->transportCosts = 5000;
            $eventInscription->visitDay = 'jour_2';
            $eventInscription->transport = 'train';

            $eventInscription->addPayment($payment = new Payment($uuid = Uuid::uuid4(), $eventInscription, $eventInscription->transportCosts, ['orderID' => $uuid->toString()]));

            if (0 === $i % 2) {
                $eventInscription->addPayment($payment = new Payment($uuid = Uuid::uuid4(), $eventInscription, $eventInscription->transportCosts, ['orderID' => $uuid->toString()]));
                $payment->addStatus(new PaymentStatus($payment, ['orderID' => $uuid->toString(), 'STATUS' => 9, 'AMOUNT' => $eventInscription->transportCosts]));
                $eventInscription->status = InscriptionStatusEnum::PAYMENT_CONFIRMED;
            } else {
                $payment->addStatus(new PaymentStatus($payment, ['orderID' => $uuid->toString(), 'STATUS' => 5, 'AMOUNT' => $eventInscription->transportCosts]));
            }
        }

        $manager->flush();
    }
}
