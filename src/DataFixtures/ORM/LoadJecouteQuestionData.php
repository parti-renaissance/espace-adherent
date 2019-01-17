<?php

namespace AppBundle\DataFixtures\ORM;

use AppBundle\DataFixtures\AutoIncrementResetter;
use AppBundle\Entity\Jecoute\Choice;
use AppBundle\Entity\Jecoute\Question;
use AppBundle\Jecoute\SurveyQuestionTypeEnum;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class LoadJecouteQuestionData extends Fixture
{
    const QUESTIONS = [
        'question-1' => [
            'content' => 'Ceci est-il un champ libre ?',
            'type' => SurveyQuestionTypeEnum::SIMPLE_FIELD,
        ],
        'question-2' => [
            'content' => 'Est-ce une question à choix multiple ?',
            'type' => SurveyQuestionTypeEnum::MULTIPLE_CHOICE_TYPE,
            'choices' => [
                'Réponse A',
                'Réponse B',
            ],
        ],
        'question-3' => [
            'content' => 'Est-ce une question à choix unique ?',
            'type' => SurveyQuestionTypeEnum::UNIQUE_CHOICE_TYPE,
            'choices' => [
                'Réponse unique 1',
                'Réponse unique 2',
            ],
        ],
        'question-4' => [
            'content' => 'Question du 2ème questionnaire, avec champ libre.',
            'type' => SurveyQuestionTypeEnum::SIMPLE_FIELD,
        ],
    ];

    public function load(ObjectManager $manager)
    {
        AutoIncrementResetter::resetAutoIncrement($manager, 'jecoute_question');
        AutoIncrementResetter::resetAutoIncrement($manager, 'jecoute_choice');

        foreach (self::QUESTIONS as $code => $data) {
            $question = new Question($data['content'], $data['type']);

            if (array_key_exists('choices', $data)) {
                $i = 1;

                foreach ($data['choices'] as $choice) {
                    $choice = new Choice($choice);
                    $question->addChoice($choice);
                    $this->addReference($code.'-choice-'.$i, $choice);

                    ++$i;
                }
            }

            $manager->persist($question);
            $this->addReference($code, $question);
        }

        $manager->flush();
    }
}
