<?php

declare(strict_types=1);

namespace App\DataFixtures\ORM;

use App\Entity\TallyForm;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Uid\Uuid;

class LoadTallyFormData extends Fixture
{
    public const TALLY_FORM_1_UUID = 'a1b2c3d4-e5f6-4a5b-8c7d-9e0f1a2b3c4d';
    public const TALLY_FORM_2_UUID = 'b2c3d4e5-f6a7-5b6c-9d8e-0f1a2b3c4d5e';
    public const TALLY_FORM_3_UUID = 'c3d4e5f6-a7b8-6c7d-8e9f-1a2b3c4d5e6f';
    public const TALLY_FORM_4_UUID = 'd4e5f6a7-b8c9-7d8e-9f0a-2b3c4d5e6f7a';

    public function load(ObjectManager $manager): void
    {
        $manager->persist($this->createTallyForm(
            Uuid::fromString(self::TALLY_FORM_1_UUID),
            'Régalien',
            'convention/regalien',
            'n9MNK5',
            'convention',
            'regalien'
        ));

        $manager->persist($this->createTallyForm(
            Uuid::fromString(self::TALLY_FORM_2_UUID),
            'Économique et social',
            'convention/economique-et-social',
            '3qvLXd',
            'convention',
            'economique-et-social'
        ));

        $manager->persist($this->createTallyForm(
            Uuid::fromString(self::TALLY_FORM_3_UUID),
            'Transition écologique',
            'convention/transition-ecologique',
            'wav9EZ',
            'convention',
            'transition-ecologique'
        ));

        $manager->persist($this->createTallyForm(
            Uuid::fromString(self::TALLY_FORM_4_UUID),
            'Consultation nom',
            'consultation-nom',
            'w4VPgb',
            'consultation',
            'nom'
        ));

        $manager->flush();
    }

    private function createTallyForm(
        Uuid $uuid,
        string $title,
        string $slug,
        string $tallyId,
        ?string $utmSource = null,
        ?string $utmCampaign = null,
        bool $published = true,
    ): TallyForm {
        $form = new TallyForm($uuid);
        $form->setTitle($title);
        $form->setSlug($slug);
        $form->setTallyId($tallyId);
        $form->utmSource = $utmSource;
        $form->utmCampaign = $utmCampaign;
        $form->setPublished($published);

        return $form;
    }
}
