<?php

declare(strict_types=1);

namespace App\DataFixtures\ORM;

use App\Entity\Adherent;
use App\Entity\Administrator;
use App\Entity\Email\EmailTemplate;
use App\Scope\ScopeEnum;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Ramsey\Uuid\Uuid;

class LoadEmailTemplateData extends Fixture implements DependentFixtureInterface
{
    public const EMAIL_TEMPLATE_1_UUID = 'ba5a7294-f7a6-4710-88c8-9ceb67ad61ce';
    public const EMAIL_TEMPLATE_2_UUID = '825c3c30-f4bd-42b5-8adf-29926a02a4af';
    public const EMAIL_TEMPLATE_3_UUID = '7fc776c1-ead9-46cc-ada8-2601c49b5312';
    public const EMAIL_TEMPLATE_4_UUID = 'e280d47c-7982-473b-a3c8-fb37b534bc8a';
    public const EMAIL_TEMPLATE_5_UUID = 'fb8a8ed1-5154-4b55-b057-99853c90e1ed';

    public function load(ObjectManager $manager): void
    {
        $emailTemplate1 = $this->createEmailTemplate(
            self::EMAIL_TEMPLATE_1_UUID,
            'Campagne national d\'adhésion',
            file_get_contents(__DIR__.'/../unlayer/content.md'),
            file_get_contents(__DIR__.'/../unlayer/json_content.json'),
        );
        $emailTemplate1->setScopes([ScopeEnum::PRESIDENT_DEPARTMENTAL_ASSEMBLY]);
        $emailTemplate1->setCreatedByAdministrator($this->getReference('administrator-2', Administrator::class));

        $emailTemplate2 = $this->createEmailTemplate(
            self::EMAIL_TEMPLATE_2_UUID,
            'Campaign Newsletter 92',
            file_get_contents(__DIR__.'/../unlayer/content.md'),
            file_get_contents(__DIR__.'/../unlayer/json_content.json'),
        );
        $emailTemplate2->setCreatedByAdherent($this->getReference('president-ad-1', Adherent::class));

        $emailTemplate3 = $this->createEmailTemplate(
            self::EMAIL_TEMPLATE_3_UUID,
            'Test Template Email',
            file_get_contents(__DIR__.'/../unlayer/content.md'),
            file_get_contents(__DIR__.'/../unlayer/json_content.json'),
        );
        $emailTemplate3->setScopes([ScopeEnum::PRESIDENT_DEPARTMENTAL_ASSEMBLY]);
        $emailTemplate3->setCreatedByAdministrator($this->getReference('administrator-2', Administrator::class));

        $emailTemplate4 = $this->createEmailTemplate(
            self::EMAIL_TEMPLATE_4_UUID,
            'Campaign Newsletter 77',
            file_get_contents(__DIR__.'/../unlayer/content.md'),
            file_get_contents(__DIR__.'/../unlayer/json_content.json'),
        );
        $emailTemplate4->setCreatedByAdherent($this->getReference('adherent-5', Adherent::class));

        $emailTemplate5 = $this->createEmailTemplate(
            self::EMAIL_TEMPLATE_5_UUID,
            'Test Template Email 77 & 75',
            file_get_contents(__DIR__.'/../unlayer/content.md'),
            file_get_contents(__DIR__.'/../unlayer/json_content.json'),
        );
        $emailTemplate5->setScopes([ScopeEnum::PRESIDENT_DEPARTMENTAL_ASSEMBLY]);
        $emailTemplate5->addZone(LoadGeoZoneData::getZoneReference($manager, 'zone_department_77'));
        $emailTemplate5->addZone(LoadGeoZoneData::getZoneReference($manager, 'zone_department_75'));
        $emailTemplate5->setCreatedByAdministrator($this->getReference('administrator-2', Administrator::class));

        $manager->persist($emailTemplate = $this->createEmailTemplate(
            Uuid::uuid4()->toString(),
            'Template email statutaire',
            file_get_contents(__DIR__.'/../unlayer/content.md'),
            file_get_contents(__DIR__.'/../unlayer/json_content.json'),
        ));
        $emailTemplate->subject = 'Email statutaire - {{_scope:"zone_name"}}';
        $emailTemplate->subjectEditable = false;
        $emailTemplate->setScopes([ScopeEnum::PRESIDENT_DEPARTMENTAL_ASSEMBLY]);
        $emailTemplate->setCreatedByAdministrator($this->getReference('administrator-2', Administrator::class));
        $emailTemplate->isStatutory = true;

        $manager->persist($emailTemplate1);
        $manager->persist($emailTemplate2);
        $manager->persist($emailTemplate3);
        $manager->persist($emailTemplate4);
        $manager->persist($emailTemplate5);

        $manager->flush();
    }

    public function createEmailTemplate(
        string $uuid,
        string $label,
        string $content,
        string $jsonContent,
    ): EmailTemplate {
        $emailTemplate = new EmailTemplate(Uuid::fromString($uuid));
        $emailTemplate->setLabel($label);
        $emailTemplate->setContent($content);
        $emailTemplate->setJsonContent($jsonContent);

        return $emailTemplate;
    }

    public function getDependencies(): array
    {
        return [
            LoadAdminData::class,
            LoadAdherentData::class,
        ];
    }
}
