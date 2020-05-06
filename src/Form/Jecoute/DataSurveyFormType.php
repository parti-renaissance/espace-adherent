<?php

namespace App\Form\Jecoute;

use App\Entity\Jecoute\DataSurvey;
use App\Entity\Jecoute\LocalSurvey;
use App\Entity\Jecoute\NationalSurvey;
use App\Jecoute\AgeRangeEnum;
use App\Jecoute\GenderEnum;
use App\Jecoute\ProfessionEnum;
use App\Jecoute\SurveyTypeEnum;
use App\Repository\Jecoute\LocalSurveyRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Security;

class DataSurveyFormType extends AbstractType
{
    private $user;

    public function __construct(Security $security)
    {
        $this->user = $security->getUser();
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if (SurveyTypeEnum::LOCAL === $options['type']) {
            $builder
                ->add('survey', EntityType::class, [
                    'class' => LocalSurvey::class,
                    'query_builder' => function (LocalSurveyRepository $localSurveyRepository) {
                        return $localSurveyRepository->createSurveysForAdherentQueryBuilder($this->user);
                    },
                ])
            ;
        } else {
            $builder
                ->add('survey', EntityType::class, [
                    'class' => NationalSurvey::class,
                ])
            ;
        }

        $builder
            ->add('lastName', TextType::class, [
                'required' => false,
            ])
            ->add('firstName', TextType::class, [
                'required' => false,
            ])
            ->add('emailAddress', EmailType::class, [
                'required' => false,
            ])
            ->add('answers', CollectionType::class, [
                'entry_type' => DataAnswerFormType::class,
                'allow_add' => true,
                'by_reference' => false,
            ])
            ->add('postalCode', TextType::class)
            ->add('profession', ChoiceType::class, [
                'choices' => ProfessionEnum::all(),
            ])
            ->add('ageRange', ChoiceType::class, [
                'choices' => AgeRangeEnum::all(),
            ])
            ->add('gender', ChoiceType::class, [
                'choices' => GenderEnum::all(),
            ])
            ->add('genderOther', TextType::class)
            ->add('agreedToStayInContact', CheckboxType::class)
            ->add('agreedToContactForJoin', CheckboxType::class)
            ->add('agreedToTreatPersonalData', CheckboxType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefault('data_class', DataSurvey::class)
            ->setRequired('type')
            ->setAllowedValues('type', SurveyTypeEnum::toArray())
        ;
    }
}
