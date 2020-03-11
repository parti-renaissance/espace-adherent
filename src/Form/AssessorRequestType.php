<?php

namespace AppBundle\Form;

use AppBundle\Assessor\AssessorRequestCommand;
use AppBundle\Assessor\AssessorRequestEnum;
use AppBundle\Entity\AssessorOfficeEnum;
use AppBundle\Intl\FranceCitiesBundle;
use AppBundle\VotePlace\VotePlaceManager;
use Misd\PhoneNumberBundle\Form\Type\PhoneNumberType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\ChoiceList\Loader\CallbackChoiceLoader;
use Symfony\Component\Form\Extension\Core\Type\BirthdayType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
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
    /** @var VotePlaceManager */
    private $votePlaceManager;

    public function __construct(VotePlaceManager $votePlaceManager)
    {
        $this->votePlaceManager = $votePlaceManager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        switch ($options['transition']) {
            case AssessorRequestEnum::TRANSITION_FILL_PERSONAL_INFO:
                $builder
                    ->add('gender', GenderType::class)
                    ->add('lastName', TextType::class)
                    ->add('firstName', TextType::class)
                    ->add('birthName', TextType::class)
                    ->add('address', TextType::class)
                    ->add('postalCode', TextType::class)
                    ->add('city', TextType::class)
                    ->add('voteCity', TextType::class)
                    ->add('officeNumber', TextType::class)
                    ->add('phone', PhoneNumberType::class, [
                        'widget' => PhoneNumberType::WIDGET_COUNTRY_CHOICE,
                    ])
                    ->add('emailAddress', EmailType::class)
                    ->add('birthdate', BirthdayType::class, [
                        'widget' => 'choice',
                        'years' => $options['years'],
                        'placeholder' => [
                            'year' => 'AAAA',
                            'month' => 'MM',
                            'day' => 'JJ',
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
                    ->add('assessorCountry', UnitedNationsCountryType::class)
                    ->add('office', ChoiceType::class, [
                        'choices' => AssessorOfficeEnum::CHOICES,
                    ])
                    ->add('votePlaceWishes', ChoiceType::class, [
                        'multiple' => true,
                    ])
                    ->add('reachable', CheckboxType::class, [
                        'required' => false,
                    ])
                    ->add('acceptDataTreatment', CheckboxType::class, [
                        'mapped' => false,
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
        $years = range((int) date('Y') - 15, (int) date('Y') - 120);

        $resolver
            ->setDefaults([
                'data_class' => AssessorRequestCommand::class,
                'validation_groups' => function (Options $options) {
                    return $options['transition'];
                },
                'years' => array_combine($years, $years),
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
            $assessorInseeCode = $command->getAssessorInseeCode();
        } else {
            $assessorCountry = $command['assessorCountry'];
            $assessorPostalCode = $command['assessorPostalCode'];
            $assessorInseeCode = $command['assessorInseeCode'];
        }

        if ((!empty($assessorPostalCode) && 'FR' === $assessorCountry) || 'FR' !== $assessorCountry) {
            $formEvent->getForm()
                ->add('votePlaceWishes', ChoiceType::class, [
                    'choice_loader' => new CallbackChoiceLoader(function () use ($assessorCountry, $assessorInseeCode) {
                        return array_flip($this->votePlaceManager->getVotePlaceWishesByCountryOrInseeCode(
                            $assessorCountry, $assessorInseeCode
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
        $data = FranceCitiesBundle::$cities[$postalCode];

        $cities = [];
        foreach ($data as $inseeCode => $cityName) {
            $cities[$inseeCode] = $cityName;
        }

        return $cities;
    }
}
