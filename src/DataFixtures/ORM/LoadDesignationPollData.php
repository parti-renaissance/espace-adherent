<?php

namespace App\DataFixtures\ORM;

use App\Entity\VotingPlatform\Designation\Poll\Poll;
use App\Entity\VotingPlatform\Designation\Poll\PollQuestion;
use App\Entity\VotingPlatform\Designation\Poll\QuestionChoice;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class LoadDesignationPollData extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $manager->persist($poll = new Poll());
        $poll->label = 'Petit questionnaire';
        $poll->addQuestion($question = new PollQuestion());
        $question->content = 'Est-ce qu\'il fait beau aujourd\'hui ?';

        $question->addChoice($choice = new QuestionChoice());
        $choice->label = 'Oui';

        $question->addChoice($choice = new QuestionChoice());
        $choice->label = 'Non, pas vraiment';
        $this->setReference('designation-poll-1', $poll);

        $manager->flush();
    }
}
