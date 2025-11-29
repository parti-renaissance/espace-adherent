<?php

declare(strict_types=1);

namespace App\DataFixtures\ORM;

use App\Entity\AdherentStaticLabelCategory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class LoadAdherentStaticLabelCategoryData extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $manager->persist($this->create('burex', 'Bureau exécutif'));
        $manager->persist($this->create('meeting', 'Meeting'));

        $manager->flush();
    }

    private function create(
        string $code,
        string $label,
        bool $sync = false,
    ): AdherentStaticLabelCategory {
        $category = new AdherentStaticLabelCategory();

        $category->code = $code;
        $category->label = $label;
        $category->sync = $sync;

        $this->setReference('adherent-static-label-category-'.$code, $category);

        return $category;
    }
}
