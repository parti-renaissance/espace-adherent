<?php

declare(strict_types=1);

namespace App\DataFixtures\ORM;

use App\Entity\Adherent;
use App\Entity\Administrator;
use App\Entity\Jecoute\Riposte;
use App\Riposte\RiposteOpenGraphHandler;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Ramsey\Uuid\Uuid;

class LoadJecouteRiposteData extends Fixture implements DependentFixtureInterface
{
    public const RIPOSTE_1_UUID = '220bd36e-4ac4-488a-8473-8e99a71efba4';
    public const RIPOSTE_2_UUID = 'ff4a352e-9762-4da7-b9f3-a8bfdbce63c1';
    public const RIPOSTE_3_UUID = '10ac465f-a2f9-44f1-9d80-8f2653a1b496';
    public const RIPOSTE_4_UUID = '80b2eb70-38c3-425e-8c1d-a90e84e1a4b3';

    private $riposteOpenGraphHandler;

    public function __construct(RiposteOpenGraphHandler $riposteOpenGraphHandler)
    {
        $this->riposteOpenGraphHandler = $riposteOpenGraphHandler;
    }

    public function load(ObjectManager $manager): void
    {
        $riposteTodayLast = $this->createRiposte(
            self::RIPOSTE_1_UUID,
            'La plus récente riposte d\'aujourd\'hui avec un URL et notification',
            'Le texte de la plus récente riposte d\'aujourd\'hui avec un lien http://riposte.fr',
            'https://a-repondre.fr',
        );
        $riposteTodayLast->incrementNbRipostes();
        $riposteTodayLast->incrementNbSourceViews();
        $riposteTodayLast->incrementNbViews();
        $riposteTodayLast->incrementNbDetailViews();

        $riposte12hoursWithoutNotification = $this->createRiposte(
            self::RIPOSTE_2_UUID,
            'La riposte avec URL et sans notification',
            'Le texte de la riposte avec URL et sans notification',
            'https://a-repondre.fr',
            '-12 hours',
            false,
            true,
            $this->getReference('deputy-75-1', Adherent::class)
        );
        $riposte12hoursWithoutNotification->incrementNbRipostes();
        $riposte12hoursWithoutNotification->incrementNbViews();

        $riposteTodayDisabled = $this->createRiposte(
            self::RIPOSTE_3_UUID,
            'La riposte d\'aujourd\'hui désactivée',
            'Le texte de la riposte d\'aujourd\'hui désactivée',
            'https://a-repondre.fr',
            '-2 hours',
            true,
            false
        );

        $riposte2daysAgo = $this->createRiposte(
            self::RIPOSTE_4_UUID,
            'La riposte d\'avant-hier avec un URL et notification',
            'Le texte de la riposte d\'avant-hier avec un lien http://riposte.fr',
            'https://a-repondre-avant-hier.fr',
            '-2 days'
        );

        $manager->persist($riposteTodayLast);
        $manager->persist($riposte12hoursWithoutNotification);
        $manager->persist($riposteTodayDisabled);
        $manager->persist($riposte2daysAgo);

        $manager->flush();
    }

    private function createRiposte(
        string $uuid,
        string $title,
        string $body,
        string $sourceUrl,
        string $createdAt = 'now',
        bool $withNotification = true,
        bool $enabled = true,
        ?Adherent $author = null,
        ?Administrator $admin = null,
    ): Riposte {
        $riposte = new Riposte(Uuid::fromString($uuid), $withNotification, $enabled);
        $riposte->setTitle($title);
        $riposte->setBody($body);
        $riposte->setSourceUrl($sourceUrl);
        $riposte->setCreatedAt(new \DateTime($createdAt));
        if ($author) {
            $riposte->setAuthor($author);
        }

        if (!$author && !$admin) {
            $riposte->setCreatedBy($this->getReference('administrator-2', Administrator::class));
        }

        $this->riposteOpenGraphHandler->handle($riposte);

        return $riposte;
    }

    public function getDependencies(): array
    {
        return [
            LoadAdminData::class,
            LoadAdherentData::class,
        ];
    }
}
