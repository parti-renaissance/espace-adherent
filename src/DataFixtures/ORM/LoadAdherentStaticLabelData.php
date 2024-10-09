<?php

namespace App\DataFixtures\ORM;

use App\Entity\AdherentStaticLabel;
use App\Entity\AdherentStaticLabelCategory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class LoadAdherentStaticLabelData extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        /** @var AdherentStaticLabelCategory $categoryBurex */
        $categoryBurex = $this->getReference('adherent-static-label-category-burex');
        /** @var AdherentStaticLabelCategory $categoryMeeting */
        $categoryMeeting = $this->getReference('adherent-static-label-category-meeting');

        $manager->persist($this->create('burex_member', 'Membre bureau exécutif', $categoryBurex));
        $manager->persist($this->create('old_burex_member', 'Ancien membre du bunrex', $categoryBurex));

        $manager->persist($this->create('lille_2024', 'Participant Meeting Lille 2024', $categoryMeeting));
        $manager->persist($this->create('nice_2024', 'Participant Meeting Nice 2024', $categoryMeeting));

        $manager->flush();
    }

    private function create(
        string $code,
        string $label,
        AdherentStaticLabelCategory $category,
    ): AdherentStaticLabel {
        $staticLabel = new AdherentStaticLabel();

        $staticLabel->code = $code;
        $staticLabel->label = $label;
        $staticLabel->category = $category;

        return $staticLabel;
    }

    public function getDependencies()
    {
        return [
            LoadAdherentStaticLabelCategoryData::class,
        ];
    }
}
