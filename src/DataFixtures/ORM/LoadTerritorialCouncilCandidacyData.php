<?php

namespace App\DataFixtures\ORM;

use App\Entity\Adherent;
use App\Entity\TerritorialCouncil\Candidacy;
use App\Entity\TerritorialCouncil\CandidacyInvitation;
use App\Entity\TerritorialCouncil\TerritorialCouncil;
use App\Entity\TerritorialCouncil\TerritorialCouncilQualityEnum;
use App\Image\ImageManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class LoadTerritorialCouncilCandidacyData extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $faker = Factory::create('fr_FR');

        /** @var TerritorialCouncil $coTerrParis */
        $coTerrParis = $this->getReference('coTerr_75');

        /** @var Adherent $adherent */
        $adherent = $this->getReference('deputy-75-1');

        $candidacy = new Candidacy($adherent->getTerritorialCouncilMembership(), $coTerrParis->getCurrentElection(), $adherent->getGender());

        $candidacy->setQuality(TerritorialCouncilQualityEnum::CITY_COUNCILOR);
        $candidacy->setBiography($faker->paragraph());
        $candidacy->setFaithStatement($faker->paragraph());
        $candidacy->setIsPublicFaithStatement(true);

        $candidacy->setImage(new UploadedFile(
            __DIR__.'/../../../app/data/dist/avatar_homme_02.jpg',
            'image.jpg',
            'image/jpeg',
            null,
            null,
            true
        ));

        $candidacy->setInvitation($invitation = new CandidacyInvitation());
        $invitation->setMembership($this->getReference('adherent-19')->getTerritorialCouncilMembership());

        $manager->persist($candidacy);
        $this->getImageManager()->saveImage($candidacy);

        $manager->flush();
    }

    private function getImageManager(): ImageManager
    {
        return $this->container->get('autowired.'.ImageManager::class);
    }

    public function getDependencies()
    {
        return [
            LoadTerritorialCouncilMembershipData::class,
        ];
    }
}
