<?php

declare(strict_types=1);

namespace App\DataFixtures\ORM;

use App\Entity\SignupSource;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class LoadSignupSourceData extends Fixture
{
    private const SOURCES = [
        'newsletter' => 'Newsletter',
        'petition' => 'Pétition',
        'event' => 'Événement',
        'national_event' => 'Grand événement national',
    ];

    public function load(ObjectManager $manager): void
    {
        foreach (self::SOURCES as $code => $label) {
            $manager->persist($this->createSource($code, $label));
        }

        $manager->flush();
    }

    private function createSource(string $code, string $label): SignupSource
    {
        $source = new SignupSource();
        $source->code = $code;
        $source->label = $label;
        $source->enabled = true;

        return $source;
    }
}
