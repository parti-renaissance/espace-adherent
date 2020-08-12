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

        /** @var Adherent $adherent */
        $adherent = $this->getReference('adherent-3');

        $candidacy = new Candidacy($adherent->getTerritorialCouncilMembership(), $coTerrParis->getCurrentElection(), $adherent->getGender());

        $candidacy->setQuality(TerritorialCouncilQualityEnum::DEPARTMENT_COUNCILOR);
        $candidacy->setBiography('Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.');
        $candidacy->setFaithStatement('Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.');
        $candidacy->setIsPublicFaithStatement(true);

        $candidacy->setImage(new UploadedFile(
            __DIR__.'/../../../app/data/dist/avatar_homme_01.jpg',
            'image.jpg',
            'image/jpeg',
            null,
            null,
            true
        ));

        /** @var Adherent $adherent */
        $adherent = $this->getReference('adherent-19');
        $candidacy->setInvitation($invitation = new CandidacyInvitation());
        $invitation->setMembership($adherent->getTerritorialCouncilMembership());
        $invitation->accept();

        $invitedCandidate = new Candidacy($invitation->getMembership(), $coTerrParis->getCurrentElection(), $adherent->getGender());

        $invitedCandidate->setQuality(TerritorialCouncilQualityEnum::DEPARTMENT_COUNCILOR);
        $invitedCandidate->setBiography('Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum. Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum. Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.');
        $invitedCandidate->setImage(new UploadedFile(
            __DIR__.'/../../../app/data/dist/avatar_femme_01.jpg',
            'image.jpg',
            'image/jpeg',
            null,
            null,
            true
        ));

        $candidacy->setBinome($invitedCandidate);
        $invitedCandidate->setBinome($candidacy);
        $invitedCandidate->updateFromBinome();

        $candidacy->confirm();
        $invitedCandidate->confirm();

        $manager->persist($candidacy);
        $this->getImageManager()->saveImage($candidacy);
        $this->getImageManager()->saveImage($invitedCandidate);

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
