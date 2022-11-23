<?php

namespace App\Form\Procuration;

use App\Entity\ProcurationRequest;
use App\Form\DataTransformer\CityNameDataTransformer;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;

class ProcurationRequestType extends AbstractProcurationType
{
    private CityNameDataTransformer $cityNameDataTransformer;

    public function __construct(CityNameDataTransformer $dataTransformer)
    {
        $this->cityNameDataTransformer = $dataTransformer;
    }

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

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        switch ($options['step_uri']) {
            case ProcurationRequest::STEP_URI_VOTE:
                $builder
                    ->add('voteCountry', CountryType::class)
                    ->add('votePostalCode', TextType::class, [
                        'required' => false,
                    ])
                    ->add('voteCity', HiddenType::class, [
                        'required' => false,
                        'error_bubbling' => true,
                    ])
                    ->add('voteCityName', TextType::class, [
                        'required' => false,
                    ])
                    ->add('voteOffice', TextType::class)
                ;

                break;

            case ProcurationRequest::STEP_URI_PROFILE:
                parent::buildForm($builder, $options);
                $builder
                    ->add('voterNumber', TextType::class)
                ;

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

        $builder->addModelTransformer($this->cityNameDataTransformer);
    }

    public function getBlockPrefix(): string
    {
        return 'app_procuration_request';
    }
}
