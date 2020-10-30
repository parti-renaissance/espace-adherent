<?php

namespace App\DataFixtures\ORM;

use App\Entity\Adherent;
use App\Entity\Committee;
use App\Entity\CommitteeCandidacy;
use App\Entity\CommitteeCandidacyInvitation;
use App\Image\ImageManager;
use App\ValueObject\Genders;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class LoadCommitteeCandidacyData extends Fixture
{
    private const CANDIDACY_UUID_1 = '9780b1ca-79c1-4f53-babd-643ebb2ca3cc';
    private const CANDIDACY_UUID_2 = '5417fda6-6aeb-47ab-9da3-46d60046a3d5';
    private const CANDIDACY_UUID_3 = 'c1921620-4101-1c66-967e-86b08a720aad';

    public function load(ObjectManager $manager)
    {
        /** @var Adherent $adherent */
        $adherent = $this->getReference('assessor-1');
        /** @var Committee $committee */
        $committee = $this->getReference('committee-6');

        $voteCommitteeMembership = $adherent->getMembershipFor($committee);
        $voteCommitteeMembership->enableVote();
        $voteCommitteeMembership->addCommitteeCandidacy(
            $candidacy = new CommitteeCandidacy(
                $committee->getCommitteeElection(),
                $adherent->getGender(),
                Uuid::fromString(self::CANDIDACY_UUID_1)
            )
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
        $voteCommitteeMembership->addCommitteeCandidacy(
            $candidacy = new CommitteeCandidacy(
                $committee->getCommitteeElection(),
                Genders::FEMALE,
                Uuid::fromString(self::CANDIDACY_UUID_2)
            )
        );

        $manager->persist($candidacy);

        $adherent = $this->getReference('adherent-3');
        $committee = $this->getReference('committee-4');

        $voteCommitteeMembership = $adherent->getMembershipFor($committee);
        $voteCommitteeMembership->addCommitteeCandidacy(
            $candidacy = new CommitteeCandidacy(
                $committee->getCommitteeElection(),
                Genders::MALE,
                Uuid::fromString(self::CANDIDACY_UUID_3)
            )
        );

        $manager->persist($candidacy);

        $adherent = $this->getReference('senatorial-candidate');
        $committee = $this->getReference('committee-12');

        // Designation COMMITTEE-SUPERVISOR
        $this->createSupervisorCandidacy(
            $manager,
            $committee,
            $adherent,
            $this->getReference('municipal-manager-1'),
            __DIR__.'/../../../app/data/dist/avatar_homme_01.jpg'
        );

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            LoadAdherentData::class,
            LoadCommitteeData::class,
        ];
    }

    private function getImageManager(): ImageManager
    {
        return $this->container->get(ImageManager::class);
    }

    private function createSupervisorCandidacy(
        ObjectManager $manager,
        Committee $committee,
        Adherent $adherent,
        ?Adherent $invited,
        string $imagePath
    ): CommitteeCandidacy {
        $candidacy = new CommitteeCandidacy($committee->getCurrentElection(), $adherent->getGender());
        $candidacy->setCommitteeMembership($adherent->getMembershipFor($committee));
        $candidacy->setBiography('Voluptas ea rerum eligendi rerum ipsum optio iusto qui. Harum minima labore tempore odio doloribus sint nihil veniam. Sint voluptas et ea cum ipsa aut. Odio ut sequi at optio mollitia asperiores voluptas.');
        $candidacy->setFaithStatement('Eum earum explicabo assumenda nesciunt hic ea. Veniam magni assumenda ab fugiat dolores consequatur voluptatem. Recusandae explicabo quia voluptatem magnam.');
        $candidacy->setIsPublicFaithStatement(true);

        $candidacy->setImage(new UploadedFile(
            $imagePath,
            'image.jpg',
            'image/jpeg',
            null,
            null,
            true
        ));

        if ($invited) {
            $candidacy->setInvitation($invitation = new CommitteeCandidacyInvitation());
            $invitation->setMembership($invited->getMembershipFor($committee));
        }

        $manager->persist($candidacy);
        $this->getImageManager()->saveImage($candidacy);

        return $candidacy;
    }
}
