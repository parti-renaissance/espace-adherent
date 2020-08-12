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
use Symfony\Component\HttpFoundation\File\UploadedFile;

class LoadTerritorialCouncilCandidacyData extends Fixture
{
    public function load(ObjectManager $manager)
    {
        /** @var TerritorialCouncil $coTerrParis */
        $coTerrParis = $this->getReference('coTerr_75');

        /** @var Adherent $adherent */
        $adherent = $this->getReference('adherent-5');

        $candidacy = new Candidacy($adherent->getTerritorialCouncilMembership(), $coTerrParis->getCurrentElection(), $adherent->getGender());

        $candidacy->setQuality(TerritorialCouncilQualityEnum::CITY_COUNCILOR);
        $candidacy->setBiography('Voluptas ea rerum eligendi rerum ipsum optio iusto qui. Harum minima labore tempore odio doloribus sint nihil veniam. Sint voluptas et ea cum ipsa aut. Odio ut sequi at optio mollitia asperiores voluptas.');
        $candidacy->setFaithStatement('Eum earum explicabo assumenda nesciunt hic ea. Veniam magni assumenda ab fugiat dolores consequatur voluptatem. Recusandae explicabo quia voluptatem magnam.');
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
        $invitation->setMembership($this->getReference('adherent-12')->getTerritorialCouncilMembership());

        $manager->persist($candidacy);
        $this->getImageManager()->saveImage($candidacy);

        $manager->flush();
    }

    private function getImageManager(): ImageManager
    {
        return $this->container->get(ImageManager::class);
    }

    public function getDependencies()
    {
        return [
            LoadTerritorialCouncilMembershipData::class,
        ];
    }
}
