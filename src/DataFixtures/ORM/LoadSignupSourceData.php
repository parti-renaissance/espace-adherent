<?php

declare(strict_types=1);

namespace App\DataFixtures\ORM;

use App\Entity\SignupSource;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class LoadSignupSourceData extends Fixture
{
    private const SOURCES = [
        // Active signup channels.
        'renaissance' => ['label' => 'Renaissance', 'enabled' => true],
        'newsletter' => ['label' => 'Newsletter', 'enabled' => true],
        'petition' => ['label' => 'Pétition', 'enabled' => true],
        'event' => ['label' => 'Événement', 'enabled' => true],
        'national_event' => ['label' => 'Grand événement national', 'enabled' => true],
        'vox' => ['label' => 'Vox', 'enabled' => true],
        // Legacy backfill origins, disabled (not signup channels).
        'em' => ['label' => 'EM', 'enabled' => false],
        'avecvous' => ['label' => 'AvecVous', 'enabled' => false],
        'besoindeurope' => ['label' => "Besoin d'Europe", 'enabled' => false],
        'ensemble2024' => ['label' => 'Ensemble 2024', 'enabled' => false],
    ];

    public function load(ObjectManager $manager): void
    {
        foreach (self::SOURCES as $code => $data) {
            $source = new SignupSource();
            $source->code = $code;
            $source->label = $data['label'];
            $source->enabled = $data['enabled'];

            $manager->persist($source);
        }

        $manager->flush();
    }
}
