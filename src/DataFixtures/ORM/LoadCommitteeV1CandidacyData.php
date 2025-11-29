<?php

declare(strict_types=1);

namespace App\DataFixtures\ORM;

use App\Committee\CommitteeMembershipManager;
use App\Entity\Adherent;
use App\Entity\Committee;
use App\Entity\CommitteeCandidacy;
use App\Entity\CommitteeCandidacyInvitation;
use App\Image\ImageManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class LoadCommitteeV1CandidacyData extends Fixture implements DependentFixtureInterface
{
    public function __construct(
        private readonly ImageManager $imageManager,
        private readonly CommitteeMembershipManager $committeeMembershipManager,
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        $adherentCandidates = [
            [
                'committee' => 'committee-6',
                'with_photo' => true,
                'enable_vote' => true,
            ],
            [
                'committee' => 'committee-6',
                'enable_vote' => true,
            ],
            [
                'committee' => 'committee-4',
            ],
            [
                'count' => 4,
                'committee' => 'committee-13',
                'binome' => true,
                'confirmed' => true,
                'public_faith_statement' => true,
                'with_photo' => true,
            ],
            [
                'committee' => 'committee-14',
                'binome' => true,
                'confirmed' => true,
                'public_faith_statement' => true,
                'with_photo' => true,
            ],
            [
                'count' => 3,
                'committee' => 'committee-15',
                'binome' => true,
                'confirmed' => true,
                'public_faith_statement' => true,
                'with_photo' => true,
            ],
        ];

        foreach ($adherentCandidates as $i => $row) {
            $memberships = $this->committeeMembershipManager->getCommitteeMemberships($committee = $this->getReference($row['committee'], Committee::class));

            for ($c = 1; $c <= ($row['count'] ?? 1); ++$c) {
                $membership = $memberships[$c - 1];
                $adherent = $membership->getAdherent();
                $election = $committee->getCurrentElection();

                if (!empty($row['enable_vote'])) {
                    $membership->enableVote();
                }

                $candidacy = $this->createCandidacy($committee, $adherent, !empty($row['with_photo']));
                $candidacy->setCommitteeMembership($membership);

                if (!empty($row['binome'])) {
                    $candidacy->setFaithStatement('Eum earum explicabo assumenda nesciunt hic ea. Veniam magni assumenda ab fugiat dolores consequatur voluptatem. Recusandae explicabo quia voluptatem magnam.');
                    $candidacy->setIsPublicFaithStatement(!empty($row['public_faith_statement']));

                    if ($invited = $memberships[++$c]) {
                        $candidacy->addInvitation($invitation = new CommitteeCandidacyInvitation());
                        $invitation->setMembership($invited);

                        if (!empty($row['confirmed'])) {
                            $candidacyBinome = $this->createCandidacy($committee, $invited->getAdherent(), !empty($row['with_photo']));
                            $manager->persist($candidacyBinome);
                            $candidacy->candidateWith($candidacyBinome);
                            $candidacy->syncWithOtherCandidacies();

                            $candidacy->confirm();
                            $candidacyBinome->confirm();
                            $invitation->accept();

                            $manager->persist($candidacyBinome);
                        }
                    }
                }

                $manager->persist($candidacy);
                $this->setReference(\sprintf('committee-candidacy-%s-%d', $election->getDesignationType(), $i + 1), $candidacy);
            }
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            LoadCommitteeV1Data::class,
        ];
    }

    private function createCandidacy(
        Committee $committee,
        Adherent $adherent,
        bool $withPhoto = false,
    ): CommitteeCandidacy {
        $adherent->getMembershipFor($committee)->addCommitteeCandidacy(
            $candidacy = new CommitteeCandidacy($committee->getCommitteeElection(), $adherent->getGender())
        );
        $candidacy->setBiography('Voluptas ea rerum eligendi rerum ipsum optio iusto qui. Harum minima labore tempore odio doloribus sint nihil veniam. Sint voluptas et ea cum ipsa aut. Odio ut sequi at optio mollitia asperiores voluptas.');

        if ($withPhoto) {
            $candidacy->setImage(new UploadedFile(
                \sprintf('%s/../../../app/data/dist/avatar_%s_0%d.jpg', __DIR__, $adherent->isFemale() ? 'femme' : 'homme', random_int(1, 2)),
                'image.jpg',
                'image/jpeg',
                null,
                true
            ));

            $this->imageManager->saveImage($candidacy);
        }

        return $candidacy;
    }
}
