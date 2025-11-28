<?php

declare(strict_types=1);

namespace App\DataFixtures\ORM;

use App\Entity\AppAlert;
use App\JeMengage\Alert\AlertTypeEnum;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class LoadAppAlertData extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $alert = new AppAlert();
        $alert->label = 'Nouvelle consultation';
        $alert->beginAt = new \DateTime('-1 day');
        $alert->endAt = new \DateTime('+1 month');
        $alert->isActive = true;
        $alert->type = AlertTypeEnum::ALERT;
        $alert->title = 'Une nouvelle consultation est disponible !';
        $alert->description = 'Consultez la nouvelle proposition de loi et donnez votre avis.';
        $alert->ctaLabel = 'Voir la consultation';
        $alert->ctaUrl = '/consultations/123';
        $alert->withMagicLink = true;
        $manager->persist($alert);

        $alert = new AppAlert();
        $alert->label = 'Alerte désactivée';
        $alert->beginAt = new \DateTime('-1 day');
        $alert->endAt = new \DateTime('+1 month');
        $alert->isActive = false;
        $alert->type = AlertTypeEnum::ALERT;
        $alert->title = 'Une nouvelle consultation est disponible !';
        $alert->description = 'Consultez la nouvelle proposition de loi et donnez votre avis.';
        $alert->ctaLabel = 'Voir la consultation';
        $alert->ctaUrl = '/consultations/123';
        $alert->withMagicLink = true;
        $manager->persist($alert);

        $manager->flush();
    }
}
