<?php

namespace App\DataFixtures\ORM;

use App\Entity\Administrator;
use App\Entity\QrCode;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Ramsey\Uuid\Uuid;

class LoadQrCodeData extends AbstractFixtures implements DependentFixtureInterface
{
    public const QR_CODE_1_UUID = '5d54f136-dce3-41fd-9a19-f8274f1cc2a9';
    public const QR_CODE_2_UUID = '9324d566-5f5a-449a-873e-e204154ff939';

    public function load(ObjectManager $manager)
    {
        /** @var Administrator $admin */
        $admin = $this->getReference('administrator-2');

        $qrCode1 = $this->createQrCode(
            self::QR_CODE_1_UUID,
            'QR Code avec redirection interne',
            'http://enmarche.code/emmanuel-macron'
        );
        $qrCode1->setCreatedBy($admin);

        $qrCode2 = $this->createQrCode(
            self::QR_CODE_2_UUID,
            'QR Code avec redirection externe',
            'https://pourunecause.fr/creer-une-cause',
            10
        );
        $qrCode2->setCreatedBy($admin);

        $manager->persist($qrCode1);
        $manager->persist($qrCode2);

        $manager->flush();
    }

    private function createQrCode(string $uuid, string $name, string $url, int $count = 0): QrCode
    {
        return new QrCode(Uuid::fromString($uuid), $name, $url, $count);
    }

    public function getDependencies()
    {
        return [
            LoadAdminData::class,
        ];
    }
}
