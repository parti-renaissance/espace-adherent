<?php

namespace App\Form;

use App\Election\VoteListNuanceEnum;
use App\Entity\Election\VoteResultList;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class VoteResultListType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('label', TextType::class, ['label' => false])
            ->add('nuance', ChoiceType::class, [
                'label' => false,
                'required' => false,
                'placeholder' => 'Nuance',
                'choices' => VoteListNuanceEnum::getChoices(),
                'choice_label' => function (string $code, string $label) {
                    return sprintf('%s - %s', $code, $label);
                },
            ])
            ->add('adherentCount', IntegerType::class, [
                'label' => false,
                'required' => false,
                'attr' => [
                    'min' => 0,
                ],
            ])
            ->add('eligibleCount', IntegerType::class, [
                'label' => false,
                'required' => false,
                'attr' => [
                    'min' => 0,
                ],
            ])
            ->add('position', IntegerType::class, [
                'label' => false,
                'required' => false,
                'attr' => [
                    'min' => 0,
                ],
            ])
            ->add('candidateFirstName', TextType::class, [
                'label' => false,
                'required' => false,
            ])
            ->add('candidateLastName', TextType::class, [
                'label' => false,
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => VoteResultList::class,
        ]);
    }
}
