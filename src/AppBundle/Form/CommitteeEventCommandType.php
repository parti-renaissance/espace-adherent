<?php

namespace AppBundle\Form;

use AppBundle\Committee\Event\CommitteeEventCommand;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CommitteeEventCommandType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class)
            ->add('category', EventCategoryType::class)
            ->add('description', TextareaType::class)
            ->add('address', AddressType::class)
            ->add('beginAt', DateTimeType::class, [
                'years' => $options['years'],
                'minutes' => $options['minutes'],
            ])
            ->add('finishAt', DateTimeType::class, [
                'years' => $options['years'],
                'minutes' => $options['minutes'],
            ])
            ->add('capacity', TextType::class, [
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $years = range((int) date('Y'), (int) date('Y') + 5);

        $resolver->setDefaults([
            'data_class' => CommitteeEventCommand::class,
            'years' => array_combine($years, $years),
            'minutes' => [
                '00' => '0',
                '15' => '15',
                '30' => '30',
                '45' => '45',
            ],
        ]);
    }

    public function getBlockPrefix()
    {
        return 'committee_event';
    }
}
