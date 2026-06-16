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
        $manager->persist($this->createAlert(
            'Alerte fin demain',
            'Une alerte de test se termine demain !',
            '+1 day',
            '/consultations/fin-demain'
        ));
        $manager->persist($this->createAlert(
            'Alerte fin dans trois jours',
            'Une alerte de test se termine dans trois jours !',
            '+3 days',
            '/consultations/fin-trois-jours'
        ));
        $manager->persist($this->createAlert(
            'Alerte fin dans sept jours',
            'Une alerte de test se termine dans sept jours !',
            '+7 days',
            '/consultations/fin-sept-jours'
        ));
        $manager->persist($this->createAlert(
            'Nouvelle consultation',
            'Une nouvelle consultation est disponible !',
            '+1 month',
            '/consultations/123',
            'Consultez la nouvelle proposition de loi et donnez votre avis.',
            'Voir la consultation'
        ));
        $manager->persist($this->createAlert(
            'Alerte désactivée',
            'Une nouvelle consultation est disponible !',
            '+1 month',
            '/consultations/123',
            'Consultez la nouvelle proposition de loi et donnez votre avis.',
            'Voir la consultation',
            false
        ));

        $manager->flush();
    }

    private function createAlert(
        string $label,
        string $title,
        string $endAt,
        string $ctaUrl,
        string $description = 'Alerte temporaire pour vérifier le tri par date de fin.',
        string $ctaLabel = 'Tester',
        bool $isActive = true,
    ): AppAlert {
        $alert = new AppAlert();
        $alert->label = $label;
        $alert->beginAt = new \DateTime('-1 day');
        $alert->endAt = new \DateTime($endAt);
        $alert->isActive = $isActive;
        $alert->type = AlertTypeEnum::ALERT;
        $alert->title = $title;
        $alert->description = $description;
        $alert->ctaLabel = $ctaLabel;
        $alert->ctaUrl = $ctaUrl;
        $alert->withMagicLink = true;

        return $alert;
    }
}
