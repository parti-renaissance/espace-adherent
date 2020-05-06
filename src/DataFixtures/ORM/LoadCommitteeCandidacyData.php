<?php

namespace App\DataFixtures\ORM;

use App\Entity\Adherent;
use App\Entity\Committee;
use App\Entity\CommitteeCandidacy;
use App\Image\ImageManager;
use App\ValueObject\Genders;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class LoadCommitteeCandidacyData extends Fixture
{
    public function load(ObjectManager $manager)
    {
        /** @var Adherent $adherent */
        $adherent = $this->getReference('assessor-1');
        /** @var Committee $committee */
        $committee = $this->getReference('committee-6');

        $voteCommitteeMembership = $adherent->getMembershipFor($committee);
        $voteCommitteeMembership->enableVote();
        $voteCommitteeMembership->setCommitteeCandidacy(
            $candidacy = new CommitteeCandidacy($committee->getCommitteeElection(), $adherent->getGender())
        );
        $candidacy->setBiography('Voici ma plus belle candidature. Votez pour moi uniquement!');
        $candidacy->setImage(new UploadedFile(
            __DIR__.'/../../../app/data/dist/avatar_homme_02.jpg',
            'image.jpg',
            'image/jpeg',
            null,
            null,
            true
        ));

        $manager->persist($candidacy);
        $this->getImageManager()->saveImage($candidacy);

        $this->setReference('committee-candidacy-1', $candidacy);

        $adherent = $this->getReference('adherent-2');

        $voteCommitteeMembership = $adherent->getMembershipFor($committee);
        $voteCommitteeMembership->enableVote();
        $voteCommitteeMembership->setCommitteeCandidacy(
            $candidacy = new CommitteeCandidacy($committee->getCommitteeElection(), Genders::FEMALE)
        );

        $manager->persist($candidacy);

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            LoadAdherentData::class,
        ];
    }

    private function getImageManager(): ImageManager
    {
        return $this->container->get('autowired.'.ImageManager::class);
    }
}
