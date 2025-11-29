<?php

declare(strict_types=1);

namespace App\DataFixtures\ORM;

use App\Entity\ElectedRepresentative\ElectedRepresentative;
use App\Entity\ElectedRepresentative\Payment;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Ramsey\Uuid\Uuid;

class LoadElectedRepresentativePaymentData extends Fixture implements DependentFixtureInterface
{
    public const PAYMENT_01_UUID = 'e7e619bb-b729-41ef-bacf-b67f8f1fb924';
    public const PAYMENT_02_UUID = '68b79f9b-d356-4779-9155-00b70fee7b56';

    public function load(ObjectManager $manager): void
    {
        /** @var ElectedRepresentative $erDepartment92 */
        $erDepartment92 = $this->getReference('elected-representative-dpt-92', ElectedRepresentative::class);

        $payment = new Payment(Uuid::fromString(self::PAYMENT_01_UUID));
        $payment->electedRepresentative = $erDepartment92;
        $payment->ohmeId = '12345789';
        $payment->date = new \DateTime('2023-03-16');
        $payment->method = 'IBAN';
        $payment->status = 'confirmed';
        $payment->amount = 50;

        $erDepartment92->addPayment($payment);

        $manager->persist($payment);

        $payment = new Payment(Uuid::fromString(self::PAYMENT_02_UUID));
        $payment->electedRepresentative = $erDepartment92;
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
            LoadElectedRepresentativeData::class,
        ];
    }
}
