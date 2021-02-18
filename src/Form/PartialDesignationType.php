<?php

namespace App\Form;

use App\VotingPlatform\Designation\CreatePartialDesignationCommand;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PartialDesignationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $dateTimeOptions = [
            'min_date' => new \DateTime(),
            'minute_increment' => 15,
        ];

        $builder
            ->add('voteStartDate', DateTimePickerType::class, $dateTimeOptions)
            ->add('voteEndDate', DateTimePickerType::class, $dateTimeOptions)
            ->add('message', PurifiedTextareaType::class, [
                'attr' => [
                    'maxlength' => 5000,
                ],
                'filter_emojis' => true,
                'purifier_type' => 'basic_content',
                'with_character_count' => true,
            ])
            ->add('back', SubmitType::class)
            ->add('next', SubmitType::class)
            ->add('confirm', SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(['data_class' => CreatePartialDesignationCommand::class]);
    }
}
