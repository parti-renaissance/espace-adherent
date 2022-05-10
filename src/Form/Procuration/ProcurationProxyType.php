<?php

namespace App\Form\Procuration;

use App\Entity\Geo\Zone;
use App\Entity\ProcurationProxy;
use App\Form\UnitedNationsCountryType;
use App\Form\ZoneAutoCompleteType;
use Misd\PhoneNumberBundle\Form\Type\PhoneNumberType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Count;
use Symfony\Component\Validator\Constraints\IsTrue;

class ProcurationProxyType extends AbstractProcurationType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver
            ->setDefaults([
                'data_class' => ProcurationProxy::class,
                'validation_groups' => ['front'],
            ])
        ;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder
            ->add('cityName', TextType::class)
            ->add('otherVoteCities', ZoneAutoCompleteType::class, [
                'placeholder' => false,
                'page_limit' => 50,
                'remote_params' => [
                    'active_only' => true,
                    'types' => [Zone::CITY, Zone::BOROUGH],
                ],
            ])
            ->add('phone', PhoneNumberType::class, [
                'widget' => PhoneNumberType::WIDGET_COUNTRY_CHOICE,
            ])
            ->add('voterNumber', TextType::class)
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
            ])
            ->add('voteOffice', TextType::class)
            ->add('proxiesCount', ChoiceType::class, [
                'expanded' => true,
                'choices' => [
                    '1' => 1,
                    '2' => 2,
                    '3' => 3,
                ],
            ])
            ->add('electionRounds', ElectionRoundsChoiceType::class, [
                'election_context' => $options['election_context'],
                'constraints' => new Count([
                    'min' => 1,
                    'minMessage' => 'procuration.election_rounds.min_count',
                    'groups' => ['front'],
                ]),
            ])
            ->add('reachable', CheckboxType::class, [
                'required' => false,
            ])
            ->add('conditions', CheckboxType::class, [
                'mapped' => false,
                'constraints' => new IsTrue([
                    'message' => 'procuration.proposal_conditions.required',
                    'groups' => ['front'],
                ]),
            ])
            ->add('authorization', CheckboxType::class, [
                'mapped' => false,
                'constraints' => new IsTrue([
                    'message' => 'procuration.authorization.required',
                    'groups' => ['front'],
                ]),
            ])
        ;
    }

    public function getBlockPrefix()
    {
        return 'app_procuration_proposal';
    }
}
