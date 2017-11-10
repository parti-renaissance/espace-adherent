<?php

namespace AppBundle\Form;

use AppBundle\Entity\Committee;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class CoordinatorCommitteeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('id', HiddenType::class)
            ->add('coordinatorComment', TextareaType::class, [
                'required' => true,
                'filter_emojis' => true,
                'attr' => [
                    'placeholder' => 'En laissant un commentaire sur le créateur, restez toujours convenable !',
                ],
                'constraints' => [
                    new NotBlank(),
                    new Length(['min' => 10]),
                ],
            ])
            ->add('accept', SubmitType::class)
            ->add('refuse', SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Committee::class,
        ]);
    }

    public function getBlockPrefix()
    {
        return 'coordinator_committee';
    }
}
