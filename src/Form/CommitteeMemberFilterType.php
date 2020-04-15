<?php

namespace AppBundle\Form;

use AppBundle\Event\Filter\ListFilterObject;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CommitteeMemberFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('ageMin', IntegerType::class, ['required' => false])
            ->add('ageMax', IntegerType::class, ['required' => false])
            ->add('firstName', TextType::class, ['required' => false])
            ->add('city', TextType::class, ['required' => false])
            ->add('registeredSince', DateType::class, ['required' => false, 'widget' => 'single_text', 'html5' => true])
            ->add('registeredUntil', DateType::class, ['required' => false, 'widget' => 'single_text', 'html5' => true])
            ->add('joinedSince', DateType::class, ['required' => false, 'widget' => 'single_text', 'html5' => true])
            ->add('joinedUntil', DateType::class, ['required' => false, 'widget' => 'single_text', 'html5' => true])
            //->add('votersOnly', CheckboxType::class, ['required' => false])
            ->add('sort', HiddenType::class, ['required' => false])
            ->add('order', HiddenType::class, ['required' => false])
            ->add('subscribed', ChoiceType::class, ['required' => false, 'placeholder' => 'common.all', 'choices' => [
                'common.adherent.subscribed' => true,
                'common.adherent.unsubscribed' => false,
            ]])
        ;
    }

    public function getBlockPrefix()
    {
        return 'filter';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ListFilterObject::class,
        ]);
    }
}
