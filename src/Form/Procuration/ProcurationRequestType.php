<?php

namespace AppBundle\Form\Procuration;

use AppBundle\Entity\ProcurationRequest;
use AppBundle\Form\UnitedNationsCountryType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;

class ProcurationRequestType extends AbstractProcurationType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver
            ->setDefaults([
                'data_class' => ProcurationRequest::class,
                'validation_groups' => function (Options $options) {
                    if (ProcurationRequest::isFinalStepUri($options['step_uri'])) {
                        // Final step, makes sure all groups are valid
                        return ProcurationRequest::STEPS;
                    }

                    return [ProcurationRequest::getStepForUri($options['step_uri'])];
                },
            ])
            ->setRequired(['step_uri'])
            ->setAllowedValues('step_uri', ProcurationRequest::STEP_URIS)
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        switch ($options['step_uri']) {
            case ProcurationRequest::STEP_URI_VOTE:
                $builder
                    ->add('voteCountry', UnitedNationsCountryType::class)
                    ->add('votePostalCode', TextType::class, [
                        'required' => false,
                    ])
                    ->add('voteCity', HiddenType::class, [
                        'required' => false,
                        'error_bubbling' => true,
                    ])
                    ->add('voteCityName', TextType::class, [
                        'required' => false,
                        'filter_emojis' => true,
                    ])
                    ->add('voteOffice', TextType::class)
                ;

                break;

            case ProcurationRequest::STEP_URI_PROFILE:
                parent::buildForm($builder, $options);
                break;

            case ProcurationRequest::STEP_URI_ELECTION_ROUNDS:
                $builder
                    ->add('requestFromFrance', ChoiceType::class, [
                        'label' => 'Type',
                        'choices' => [
                            'France' => true,
                            'Étranger' => false,
                        ],
                        'expanded' => true,
                    ])
                    ->add('electionRounds', ElectionRoundsChoiceType::class, [
                        'election_context' => $options['election_context'],
                    ])
                    ->add('reason', ChoiceType::class, [
                        'choices' => [
                            'Parce que je réside dans une commune différente de celle où je suis inscrit(e) sur une liste électorale' => ProcurationRequest::REASON_RESIDENCY,
                            'Parce que je suis en vacances' => ProcurationRequest::REASON_HOLIDAYS,
                            'En raison d’obligations professionnelles' => ProcurationRequest::REASON_PROFESSIONAL,
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
                            'groups' => [ProcurationRequest::STEP_ELECTION_ROUNDS],
                        ]),
                    ])
                    ->add('reachable', CheckboxType::class, [
                        'required' => false,
                    ])
                ;

                break;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'app_procuration_request';
    }
}
