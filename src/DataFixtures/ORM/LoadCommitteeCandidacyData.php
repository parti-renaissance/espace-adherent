<?php

namespace AppBundle\DataFixtures\ORM;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\Committee;
use AppBundle\Entity\CommitteeCandidacy;
use AppBundle\Image\ImageManager;
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
