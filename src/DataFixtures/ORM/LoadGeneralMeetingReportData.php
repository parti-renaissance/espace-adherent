<?php

declare(strict_types=1);

namespace App\DataFixtures\ORM;

use App\Entity\Adherent;
use App\Entity\GeneralMeeting\GeneralMeetingReport;
use App\Entity\Geo\Zone;
use App\GeneralMeeting\GeneralMeetingReportHandler;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class LoadGeneralMeetingReportData extends Fixture implements DependentFixtureInterface
{
    public const GENERAL_MEETING_REPORT_1_UUID = '03e43b53-8845-41e2-9603-fa8893f25ed6';
    public const GENERAL_MEETING_REPORT_2_UUID = '060c0ac4-48cc-4235-aeb7-24b07af1252f';
    public const GENERAL_MEETING_REPORT_3_UUID = 'ddb49d56-5113-4780-93df-29351db6976d';

    private Generator $faker;

    public function __construct(private readonly GeneralMeetingReportHandler $generalMeetingReportHandler)
    {
        $this->faker = Factory::create('fr_FR');
    }

    public function load(ObjectManager $manager): void
    {
        /** @var Adherent $referent77 */
        $referent77 = $this->getReference('adherent-8', Adherent::class);
        /** @var Zone $zoneDepartment77 */
        $zoneDepartment77 = LoadGeoZoneData::getZoneReference($manager, 'zone_department_77');

        $generalMeetingReport = $this->createLocalGeneralMeetingReport(
            self::GENERAL_MEETING_REPORT_1_UUID,
            $referent77,
            $zoneDepartment77,
            'Premier PV d\'AG du 77',
            true,
            new \DateTime('2023-02-05 14:45:00')
        );
        $this->createFile($generalMeetingReport);
        $manager->persist($generalMeetingReport);

        $generalMeetingReport = $this->createLocalGeneralMeetingReport(
            self::GENERAL_MEETING_REPORT_2_UUID,
            $referent77,
            $zoneDepartment77,
            'Deuxième PV d\'AG du 77',
            true,
            new \DateTime('2023-02-08 19:00:00')
        );
        $this->createFile($generalMeetingReport);
        $manager->persist($generalMeetingReport);

        /** @var Adherent $referent06 */
        $referent06 = $this->getReference('renaissance-user-3', Adherent::class);
        /** @var Zone $zoneDepartment06 */
        $zoneDepartment06 = LoadGeoZoneData::getZoneReference($manager, 'zone_department_06');

        $generalMeetingReport = $this->createLocalGeneralMeetingReport(
            self::GENERAL_MEETING_REPORT_3_UUID,
            $referent06,
            $zoneDepartment06,
            'Premier PV d\'AG du 06',
            true,
            new \DateTime('2023-02-07 15:30:00')
        );
        $this->createFile($generalMeetingReport);
        $manager->persist($generalMeetingReport);

        $manager->flush();
    }

    private function createLocalGeneralMeetingReport(
        string $uuid,
        Adherent $creator,
        Zone $zone,
        string $title,
        bool $description,
        \DateTimeInterface $date,
    ): GeneralMeetingReport {
        $generalMeetingReport = $this->createGeneralMeetingReport($uuid, $title, $description, $date);
        $generalMeetingReport->setCreatedByAdherent($creator);
        $generalMeetingReport->setZone($zone);

        return $generalMeetingReport;
    }

    private function createGeneralMeetingReport(
        string $uuid,
        string $title,
        bool $description,
        \DateTimeInterface $date,
    ): GeneralMeetingReport {
        $generalMeetingReport = new GeneralMeetingReport(Uuid::fromString($uuid));
        $generalMeetingReport->setTitle($title);
        $generalMeetingReport->setDescription($description ? $this->faker->text('200') : null);
        $generalMeetingReport->setDate($date);

        return $generalMeetingReport;
    }

    private function createFile(GeneralMeetingReport $generalMeetingReport): void
    {
        $generalMeetingReport->setFile(new UploadedFile(
            __DIR__.'/../adherent_formations/formation.pdf',
            'Rapport-AG.pdf',
            'application/pdf',
            null,
            true
        ));

        $this->generalMeetingReportHandler->handleFile($generalMeetingReport);
    }

    public function getDependencies(): array
    {
        return [
            LoadAdherentData::class,
            LoadGeoZoneData::class,
        ];
    }
}
