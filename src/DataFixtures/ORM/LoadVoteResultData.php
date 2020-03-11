<?php

namespace AppBundle\DataFixtures\ORM;

use AppBundle\Entity\Election\VotePlaceResult;
use AppBundle\Entity\ElectionRound;
use AppBundle\Entity\VotePlace;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class LoadVoteResultData extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        /** @var VotePlace $assessor1VotePlace */
        $assessor1VotePlace = $this->getReference('vote-place-lille-wazemmes');

        /** @var ElectionRound $round1Legislatives */
        $round1Legislatives = $this->getReference('round-1-legislatives');
        /** @var ElectionRound $round2Legislatives */
        $round2Legislatives = $this->getReference('round-2-legislatives');

        $manager->persist($this->createVoteResult(
            $assessor1VotePlace,
            $round1Legislatives,
            1000,
            300,
            500,
            400
        ));

        $manager->persist($this->createVoteResult(
            $assessor1VotePlace,
            $round2Legislatives,
            1000,
            200,
            600,
            500
        ));

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            LoadElectionData::class,
            LoadVotePlaceData::class,
        ];
    }

    private function createVoteResult(
        VotePlace $votePlace,
        ElectionRound $electionRound,
        int $registered,
        int $abstentions,
        int $participated,
        int $expressed
    ): VotePlaceResult {
        $voteResult = new VotePlaceResult($votePlace, $electionRound);
        $voteResult->setRegistered($registered);
        $voteResult->setAbstentions($abstentions);
        $voteResult->setParticipated($participated);
        $voteResult->setExpressed($expressed);

        return $voteResult;
    }
}
