<?php

namespace AppBundle\Form\Jecoute;

use AppBundle\Entity\Jecoute\DataSurvey;
use AppBundle\Entity\Jecoute\Survey;
use AppBundle\Jecoute\AgeRangeEnum;
use AppBundle\Jecoute\GenderEnum;
use AppBundle\Jecoute\ProfessionEnum;
use AppBundle\Repository\Jecoute\SurveyRepository;
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
        $builder
            ->add('survey', EntityType::class, [
                'class' => Survey::class,
                'query_builder' => function (SurveyRepository $repository) {
                    return $repository->createSurveysForAdherentQueryBuilder($this->user);
                },
            ])
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
        $resolver->setDefault('data_class', DataSurvey::class);
    }
}
