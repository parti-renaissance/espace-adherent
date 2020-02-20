<?php

namespace AppBundle\DataFixtures\ORM;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\ElectionRound;
use AppBundle\Entity\VotePlace;
use AppBundle\Entity\VoteResult;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class LoadVoteResultData extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        /** @var Adherent $assessor1 */
        $assessor1 = $this->getReference('assessor-1');

        /** @var VotePlace $assessor1VotePlace */
        $assessor1VotePlace = $this->getReference('vote-place-lille-wazemmes');

        /** @var ElectionRound $round1Legislatives */
        $round1Legislatives = $this->getReference('round-1-legislatives');
        $round2Legislatives = $this->getReference('round-2-legislatives');

        $manager->persist($this->createVoteResult(
            $assessor1VotePlace,
            $round1Legislatives,
            $assessor1,
            1000,
            300,
            500,
            400,
            [
                'Liste A' => '200',
                'Liste B' => '150',
                'Liste C' => '50',
            ]
        ));

        $manager->persist($this->createVoteResult(
            $assessor1VotePlace,
            $round2Legislatives,
            $assessor1,
            1000,
            200,
            600,
            500,
            [
                'Liste A' => '350',
                'Liste B' => '150',
            ]
        ));

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            LoadAdherentData::class,
            LoadElectionData::class,
            LoadVotePlaceData::class,
        ];
    }

    private function createVoteResult(
        VotePlace $votePlace,
        ElectionRound $electionRound,
        Adherent $author,
        int $registered,
        int $abstentions,
        int $voters,
        int $expressed,
        array $lists
    ): VoteResult {
        $voteResult = new VoteResult($votePlace, $electionRound, $author);
        $voteResult->setRegistered($registered);
        $voteResult->setAbstentions($abstentions);
        $voteResult->setVoters($voters);
        $voteResult->setExpressed($expressed);

        foreach ($lists as $label => $votes) {
            $voteResult->addList($label, $votes);
        }

        return $voteResult;
    }
}
