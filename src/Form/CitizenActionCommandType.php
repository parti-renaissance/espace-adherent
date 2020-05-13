<?php

namespace App\Form;

use App\CitizenAction\CitizenActionCommand;
use App\Entity\CitizenActionCategory;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CitizenActionCommandType extends AbstractType
{
    public function getParent()
    {
        return BaseEventCommandType::class;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->remove('capacity')
            ->add('description', TextareaType::class, [
                'filter_emojis' => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class' => CitizenActionCommand::class,
                'event_category_class' => CitizenActionCategory::class,
            ])
        ;
    }

    public function getBlockPrefix()
    {
        return 'citizen_action';
    }
}
