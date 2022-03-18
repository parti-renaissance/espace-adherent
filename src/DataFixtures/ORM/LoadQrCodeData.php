<?php

namespace App\DataFixtures\ORM;

use App\Entity\Administrator;
use App\Entity\QrCode;
use App\QrCode\QrCodeHostEnum;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Ramsey\Uuid\Uuid;

class LoadQrCodeData extends Fixture implements DependentFixtureInterface
{
    public const QR_CODE_1_UUID = '5d54f136-dce3-41fd-9a19-f8274f1cc2a9';
    public const QR_CODE_2_UUID = '9324d566-5f5a-449a-873e-e204154ff939';
    public const QR_CODE_3_UUID = '849ec452-8104-44ba-94b7-9d633a24a895';

    public function load(ObjectManager $manager)
    {
        /** @var Administrator $admin */
        $admin = $this->getReference('administrator-2');

        $qrCode1 = $this->createQrCode(
            self::QR_CODE_1_UUID,
            'QR Code avec redirection interne',
            'http://enmarche.code/emmanuel-macron',
            QrCodeHostEnum::HOST_ENMARCHE
        );
        $qrCode1->setCreatedBy($admin);

        $qrCode2 = $this->createQrCode(
            self::QR_CODE_2_UUID,
            'QR Code avec redirection externe',
            'https://pourunecause.fr/creer-une-cause',
            QrCodeHostEnum::HOST_ENMARCHE,
            10
        );
        $qrCode2->setCreatedBy($admin);

        $qrCode3 = $this->createQrCode(
            self::QR_CODE_3_UUID,
            'QR Code avec redirection externe sur le domaine avecvous.fr',
            'https://avecvous.fr',
            QrCodeHostEnum::HOST_AVECVOUS,
            10
        );
        $qrCode3->setCreatedBy($admin);

        $manager->persist($qrCode1);
        $manager->persist($qrCode2);
        $manager->persist($qrCode3);

        $manager->flush();
    }

    private function createQrCode(string $uuid, string $name, string $url, string $host, int $count = 0): QrCode
    {
        return new QrCode(Uuid::fromString($uuid), $name, $url, $host, $count);
    }

    public function getDependencies()
    {
        return [
            LoadAdminData::class,
        ];
    }
}
