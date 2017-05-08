<?php

namespace AppBundle\Form;

use AppBundle\Entity\ProcurationRequest;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;

class ProcurationElectionsType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('electionLegislativeFirstRound', CheckboxType::class, [
                'required' => false,
            ])
            ->add('electionLegislativeSecondRound', CheckboxType::class, [
                'required' => false,
            ])
            ->add('reason', ChoiceType::class, [
                'choices' => [
                    'Parce que je réside dans une commune différente de celle où je suis inscrit(e) sur une liste électorale' => ProcurationRequest::REASON_RESIDENCY,
                    'Parce que je suis en vacances' => ProcurationRequest::REASON_HOLIDAYS,
                    'En raison d’obligations professionnelles' => ProcurationRequest::REASON_PROFESIONNAL,
                    'En raison d’un handicap' => ProcurationRequest::REASON_HANDICAP,
                    'Pour raison de santé' => ProcurationRequest::REASON_HEALTH,
                    'En raison d’assistance apportée à une personne malade ou infirme' => ProcurationRequest::REASON_HELP,
                    'En raison d’obligations de formation' => ProcurationRequest::REASON_TRAINING,
                ],
            ])
            ->add('authorization', CheckboxType::class, [
                'mapped' => false,
                'constraints' => new IsTrue([
                    'message' => 'procuration.authorization.required',
                    'groups' => ['elections'],
                ]),
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ProcurationRequest::class,
            'validation_groups' => ['vote', 'profile', 'elections'],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'app_procuration_elections';
    }
}
