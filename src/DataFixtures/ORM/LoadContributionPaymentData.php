<?php

declare(strict_types=1);

namespace App\DataFixtures\ORM;

use App\Entity\Adherent;
use App\Entity\Contribution\Payment;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Ramsey\Uuid\Uuid;

class LoadContributionPaymentData extends Fixture implements DependentFixtureInterface
{
    public const PAYMENT_01_UUID = '9b19f265-aae1-4be9-af2e-fd739e27e79a';
    public const PAYMENT_02_UUID = '6c547c74-f04d-4e6d-bc38-5aceba5d86e5';

    public function load(ObjectManager $manager): void
    {
        /** @var Adherent $erDepartment92 */
        $erDepartment92 = $this->getReference('renaissance-user-2', Adherent::class);

        $payment = new Payment(Uuid::fromString(self::PAYMENT_01_UUID));
        $payment->adherent = $erDepartment92;
        $payment->ohmeId = '12345789';
        $payment->date = new \DateTime('2023-03-16');
        $payment->method = 'IBAN';
        $payment->status = 'confirmed';
        $payment->amount = 50;

        $erDepartment92->addPayment($payment);

        $manager->persist($payment);

        $payment = new Payment(Uuid::fromString(self::PAYMENT_02_UUID));
        $payment->adherent = $erDepartment92;
        $payment->ohmeId = '987654321';
        $payment->date = new \DateTime('2023-04-16');
        $payment->method = 'IBAN';
        $payment->status = 'confirmed';
        $payment->amount = 50;

        $erDepartment92->addPayment($payment);

        $manager->persist($payment);

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            LoadAdherentData::class,
        ];
    }
}
