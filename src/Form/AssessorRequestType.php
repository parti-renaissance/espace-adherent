<?php

namespace App\Form;

use App\Address\AddressInterface;
use App\Assessor\AssessorRequestCommand;
use App\Assessor\AssessorRequestElectionRoundsEnum;
use App\Assessor\AssessorRequestEnum;
use App\Entity\AssessorOfficeEnum;
use App\FranceCities\FranceCities;
use App\VotePlace\VotePlaceManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\ChoiceList\Loader\CallbackChoiceLoader;
use Symfony\Component\Form\Extension\Core\Type\BirthdayType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AssessorRequestType extends AbstractType
{
    private VotePlaceManager $votePlaceManager;
    private FranceCities $franceCities;

    public function __construct(VotePlaceManager $votePlaceManager, FranceCities $franceCities)
    {
        $this->votePlaceManager = $votePlaceManager;
        $this->franceCities = $franceCities;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        switch ($options['transition']) {
            case AssessorRequestEnum::TRANSITION_FILL_PERSONAL_INFO:
                $builder
                    ->add('gender', GenderType::class)
                    ->add('lastName', TextType::class)
                    ->add('firstName', TextType::class)
                    ->add('address', TextType::class)
                    ->add('postalCode', TextType::class)
                    ->add('city', TextType::class)
                    ->add('country', CountryType::class, [
                        'disabled' => true,
                    ])
                    ->add('voteCity', TextType::class)
                    ->add('officeNumber', TextType::class, [
                        'required' => false,
                    ])
                    ->add('voterNumber', TextType::class)
                    ->add('phone', TelNumberType::class)
                    ->add('emailAddress', EmailType::class)
                    ->add('birthdate', BirthdayType::class, [
                        'placeholder' => [
                            'year' => 'Année',
                            'month' => 'Mois',
                            'day' => 'Jour',
                        ],
                    ])
                    ->add('birthCity', TextType::class)
                ;

                $this->addSubmitButton(
                    $builder, AssessorRequestEnum::TRANSITION_FILL_PERSONAL_INFO, 'Continuer'
                );

                break;
            case AssessorRequestEnum::TRANSITION_FILL_ASSESSOR_INFO:
                $builder
                    ->add('assessorPostalCode', TextType::class, [
                        'required' => false,
                    ])
                    ->add('assessorCity', ChoiceType::class, [
                        'required' => false,
                    ])
                    ->add('assessorCountry', CountryType::class)
                    ->add('office', ChoiceType::class, [
                        'choices' => AssessorOfficeEnum::CHOICES,
                    ])
                    ->add('electionRounds', ChoiceType::class, [
                        'choices' => AssessorRequestElectionRoundsEnum::CHOICES,
                        'multiple' => true,
                        'expanded' => true,
                    ])
                    ->add('votePlaceWishes', ChoiceType::class, [
                        'multiple' => true,
                    ])
                    ->add('reachable', CheckboxType::class, [
                        'required' => false,
                    ])
                    ->add('acceptValuesCharter', CheckboxType::class, [
                        'mapped' => false,
                    ])
                ;

                $builder->addEventListener(FormEvents::PRE_SET_DATA, [$this, 'addAssessorCityAndVotePlaceWishesType']);
                $builder->addEventListener(FormEvents::PRE_SUBMIT, [$this, 'addAssessorCityAndVotePlaceWishesType']);

                $this->addSubmitButton(
                    $builder, AssessorRequestEnum::TRANSITION_FILL_ASSESSOR_INFO, 'Dernière étape'
                );

                break;
            case AssessorRequestEnum::TRANSITION_VALID_SUMMARY:
                $this->addSubmitButton($builder, AssessorRequestEnum::TRANSITION_VALID_SUMMARY, 'Valider');

                break;
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class' => AssessorRequestCommand::class,
                'validation_groups' => function (Options $options) {
                    return $options['transition'];
                },
            ])
            ->setRequired('transition')
            ->setAllowedValues('transition', AssessorRequestEnum::TRANSITIONS)
        ;
    }

    private function addSubmitButton(FormBuilderInterface $builder, string $step, string $label): void
    {
        $builder->add($step, SubmitType::class, [
            'label' => $label,
        ]);
    }

    public function addAssessorCityAndVotePlaceWishesType(FormEvent $formEvent): void
    {
        $command = $formEvent->getData();

        if ($command instanceof AssessorRequestCommand) {
            $assessorCountry = $command->getAssessorCountry();
            $assessorPostalCode = $command->getAssessorPostalCode();
            $assessorCity = $command->getAssessorCity();
        } else {
            $assessorCountry = $command['assessorCountry'];
            $assessorPostalCode = $command['assessorPostalCode'];
            $assessorCity = $command['assessorCity'];
        }

        if ((!empty($assessorPostalCode) && !empty($assessorCity) && AddressInterface::FRANCE === $assessorCountry) || AddressInterface::FRANCE !== $assessorCountry) {
            $formEvent->getForm()
                ->add('votePlaceWishes', ChoiceType::class, [
                    'choice_loader' => new CallbackChoiceLoader(function () use ($assessorCountry, $assessorPostalCode, $assessorCity) {
                        return array_flip($this->votePlaceManager->getVotePlaceWishesByCountryOrPostalCode(
                            $assessorCountry, $assessorPostalCode, $assessorCity
                        ));
                    }),
                    'multiple' => true,
                ])
            ;
        }

        if (!empty($assessorPostalCode)) {
            $formEvent->getForm()
                ->add('assessorCity', ChoiceType::class, [
                    'choice_loader' => new CallbackChoiceLoader(function () use ($assessorPostalCode) {
                        return array_flip($this->formatCitiesByPostalCode($assessorPostalCode));
                    }),
                ])
            ;
        }
    }

    public function formatCitiesByPostalCode(string $postalCode): array
    {
        $data = $this->franceCities->findCitiesByPostalCode($postalCode);

        $cities = [];
        foreach ($data as $city) {
            $cities[$city->getName()] = $city->getName();
        }

        return $cities;
    }
}
