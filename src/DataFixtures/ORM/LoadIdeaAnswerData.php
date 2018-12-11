<?php

namespace AppBundle\DataFixtures\ORM;

use AppBundle\Entity\IdeasWorkshop\Answer;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class LoadIdeaAnswerData extends AbstractFixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $ideaPeace = $this->getReference('idea-peace');
        $questionProblem = $this->getReference('question-problem');
        $questionAnswer = $this->getReference('question-answer');
        $questionCompare = $this->getReference('question-compare');
        $questionNegativeEffect = $this->getReference('question-negative-effect');
        $questionLawImpact = $this->getReference('question-law-impact');
        $questionBudgetImpact = $this->getReference('question-budget-impact');
        $questionEcologyImpact = $this->getReference('question-ecology-impact');
        $questionGenderEquality = $this->getReference('question-gender-equality');

        $answerQuestionProblem = new Answer(
            'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Fusce aliquet, mi condimentum venenatis vestibulum, arcu neque feugiat massa, at pharetra velit sapien et elit. Sed vitae hendrerit nulla. Vivamus consectetur magna at tincidunt maximus. Aenean dictum metus vel tellus posuere venenatis.',
            $questionProblem
        );
        $ideaPeace->addAnswer($answerQuestionProblem);
        $this->addReference('answer-q-problem', $answerQuestionProblem);

        $answerQuestionAnswer = new Answer(
            'Nam nisi nunc, ornare nec elit id, porttitor vestibulum ligula. Donec enim tellus, congue non quam at, aliquam porta ex. Curabitur at eros et ex faucibus fringilla sed vel velit.',
            $questionAnswer
        );
        $ideaPeace->addAnswer($answerQuestionAnswer);
        $this->addReference('answer-q-answer', $answerQuestionAnswer);

        $answerQuestionCompare = new Answer(
            'Nam nisi nunc, ornare nec elit id, porttitor vestibulum ligula. Donec enim tellus, congue non quam at, aliquam porta ex. Curabitur at eros et ex faucibus fringilla sed vel velit.',
            $questionCompare
        );
        $ideaPeace->addAnswer($answerQuestionCompare);
        $this->addReference('answer-q-compare', $answerQuestionCompare);

        $answerQuestionNegativeEffect = new Answer(
            'Nam nisi nunc, ornare nec elit id, porttitor vestibulum ligula. Donec enim tellus, congue non quam at, aliquam porta ex. Curabitur at eros et ex faucibus fringilla sed vel velit.',
            $questionNegativeEffect
        );
        $ideaPeace->addAnswer($answerQuestionNegativeEffect);
        $this->addReference('answer-q-negative-effect', $answerQuestionNegativeEffect);

        $answerQuestionLawImpact = new Answer(
            'Nam nisi nunc, ornare nec elit id, porttitor vestibulum ligula. Donec enim tellus, congue non quam at, aliquam porta ex. Curabitur at eros et ex faucibus fringilla sed vel velit.',
            $questionLawImpact
        );
        $ideaPeace->addAnswer($answerQuestionLawImpact);
        $this->addReference('answer-q-law-impact', $answerQuestionLawImpact);

        $answerQuestionBudgetImpact = new Answer(
            'Nam nisi nunc, ornare nec elit id, porttitor vestibulum ligula. Donec enim tellus, congue non quam at, aliquam porta ex. Curabitur at eros et ex faucibus fringilla sed vel velit.',
            $questionBudgetImpact
        );
        $ideaPeace->addAnswer($answerQuestionBudgetImpact);
        $this->addReference('answer-q-budget-impact', $answerQuestionBudgetImpact);

        $answerQuestionEcologyImpact = new Answer(
            'Nam nisi nunc, ornare nec elit id, porttitor vestibulum ligula. Donec enim tellus, congue non quam at, aliquam porta ex. Curabitur at eros et ex faucibus fringilla sed vel velit.',
            $questionEcologyImpact
        );
        $ideaPeace->addAnswer($answerQuestionEcologyImpact);
        $this->addReference('answer-q-ecology-impact', $answerQuestionEcologyImpact);

        $answerQuestionGenderEquality = new Answer(
            'Nam nisi nunc, ornare nec elit id, porttitor vestibulum ligula. Donec enim tellus, congue non quam at, aliquam porta ex. Curabitur at eros et ex faucibus fringilla sed vel velit.',
            $questionGenderEquality
        );
        $ideaPeace->addAnswer($answerQuestionGenderEquality);
        $this->addReference('answer-q-gender-equality', $answerQuestionGenderEquality);

        $manager->persist($answerQuestionProblem);
        $manager->persist($answerQuestionAnswer);
        $manager->persist($answerQuestionCompare);
        $manager->persist($answerQuestionNegativeEffect);
        $manager->persist($answerQuestionLawImpact);
        $manager->persist($answerQuestionBudgetImpact);
        $manager->persist($answerQuestionEcologyImpact);
        $manager->persist($answerQuestionGenderEquality);

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            LoadIdeaData::class,
            LoadIdeaQuestionData::class,
        ];
    }
}
