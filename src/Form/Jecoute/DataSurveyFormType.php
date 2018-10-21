<?php

namespace AppBundle\Form\Jecoute;

use AppBundle\Entity\Jecoute\DataSurvey;
use AppBundle\Entity\Jecoute\Survey;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DataSurveyFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('survey', EntityType::class, [
                'class' => Survey::class,
            ])
            ->add('lastName', TextType::class, [
                'required' => false,
            ])
            ->add('firstName', TextType::class, [
                'required' => false,
            ])
            ->add('phone', TextType::class, [
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
            ->add('agreedToStayInContact', CheckboxType::class)
            ->add('agreedToJoinParisOperation', CheckboxType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class', DataSurvey::class,
            'csrf_protection' => false,
        ]);
    }
}
