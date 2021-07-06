<?php

namespace App\Form\Coalition;

use App\Entity\Event\BaseEvent;
use App\Form\BaseEventCommandType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class CoalitionEventType extends AbstractType
{
    public function getParent()
    {
        return BaseEventCommandType::class;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('coalition', EnabledCoalitionEntityType::class, [
                'constraints' => [new NotBlank()],
            ])
            ->add('mode', ChoiceType::class, [
                'choices' => array_combine(BaseEvent::MODES, BaseEvent::MODES),
                'expanded' => true,
                'choice_label' => function (string $choice) {
                    return 'common.mode.'.$choice;
                },
            ])
            ->remove('category')
            ->remove('capacity')
            ->remove('image')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(['validation_groups' => 'Default']);
    }
}
