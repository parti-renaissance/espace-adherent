<?php

namespace AppBundle\DataFixtures\ORM;

use AppBundle\DataFixtures\AutoIncrementResetter;
use AppBundle\Entity\IdeasWorkshop\Question;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class LoadIdeaQuestionData extends AbstractFixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        AutoIncrementResetter::resetAutoIncrement($manager, 'ideas_workshop_question');

        $guidelineMainFeature = $this->getReference('guideline-main-feature');
        $guidelineImplementation = $this->getReference('guideline-implementation');

        $isMandatory = true;

        $questionProblem = new Question(
            'Constat : quel problème souhaitez vous résoudre ?',
            'Expliquez, en maximum 1700 caractères (espaces compris) le problème que vous identifiez et espérez pouvoir remédier.',
            1,
            $isMandatory
        );
        $this->addReference('question-problem', $questionProblem);
        $guidelineMainFeature->addQuestion($questionProblem);

        $questionAnswer = new Question(
            'Solution : quelle réponse votre idée apporte-t-elle ? ',
            'Expliquez, en maximum 1700 caractères (espaces compris), comment votre proposition répond concrètement au problème.',
            2,
            $isMandatory
        );
        $this->addReference('question-answer', $questionAnswer);
        $guidelineMainFeature->addQuestion($questionAnswer);

        $questionCompare = new Question(
            'Comparaison : cette proposition a-t-elle déjà été mise en oeuvre ou étudiée ?',
            'Expliquez, en maximum 1700 caractères (espaces compris), si et comment cette proposition a été étudiée ou mise en oeuvre en France ou à l’étranger.',
            3
        );
        $this->addReference('question-compare', $questionCompare);
        $guidelineMainFeature->addQuestion($questionCompare);

        $questionNegativeEffect = new Question(
            'Impact : Cette proposition peut elle avoir des effets négatifs pour certains publics ?',
            'Expliquez, en maximum 1700 caractères (espaces compris), si cette proposition peut porter préjudice à certains publics (individus, entreprises, associations, ou pays) et comment il est possible d’en limiter les effets.',
            4
        );
        $this->addReference('question-negative-effect', $questionNegativeEffect);
        $guidelineMainFeature->addQuestion($questionNegativeEffect);

        $questionLawImpact = new Question(
            'Droit : votre idée suppose-t-elle de changer le droit ?',
            'Expliquez, en maximum 1700 caractères (espaces compris), si votre idée nécessite - ou non - de changer le droit en vigueur. Si oui, idéalement, précisez ce qu’il faudrait changer.',
            5
        );
        $this->addReference('question-law-impact', $questionLawImpact);
        $guidelineImplementation->addQuestion($questionLawImpact);

        $questionBudgetImpact = new Question(
            'Budget : votre idée a-t-elle un impact financier ?',
            'Expliquez, en maximum 1700 caractères (espaces compris), si votre idée entraîne directement des recettes ou des dépenses pour l’État ou les collectivités locales. Si oui, idéalement, donnez des éléments de chiffrage.',
            6
        );
        $this->addReference('question-budget-impact', $questionBudgetImpact);
        $guidelineImplementation->addQuestion($questionBudgetImpact);

        $questionEcologyImpact = new Question(
            'Environnement : votre idée a-t-elle un impact écologique ?',
            'Expliquez, en maximum 1700 caractères (espaces compris), si votre idée a des effets positifs ou négatifs sur l’environnement. Idéalement, précisez des éléments de réponse pour maximiser ou minimiser (selon les cas) ces effets.',
            7
        );
        $this->addReference('question-ecology-impact', $questionEcologyImpact);
        $guidelineImplementation->addQuestion($questionEcologyImpact);

        $questionGenderEquality = new Question(
            'Égalité femmes-hommes : votre idée a-t-elle un impact sur l’égalité entre les femmes et les hommes ?',
            'Expliquez, en maximum 1700 caractères (espaces compris), si votre idée a des effets positifs ou négatifs sur l’égalité entre les femmes et les hommes. Idéalement, donnez des éléments pour maximiser ou minimiser (selon les cas) ces effets.',
            8
        );
        $this->addReference('question-gender-equality', $questionGenderEquality);
        $guidelineImplementation->addQuestion($questionGenderEquality);

        $manager->persist($questionProblem);
        $manager->persist($questionAnswer);
        $manager->persist($questionCompare);
        $manager->persist($questionNegativeEffect);
        $manager->persist($questionLawImpact);
        $manager->persist($questionBudgetImpact);
        $manager->persist($questionEcologyImpact);
        $manager->persist($questionGenderEquality);

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            LoadIdeaGuidelineData::class,
        ];
    }
}
