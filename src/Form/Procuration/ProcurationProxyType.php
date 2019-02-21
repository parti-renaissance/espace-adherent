<?php

namespace AppBundle\Form\Procuration;

use AppBundle\Entity\ProcurationProxy;
use AppBundle\Form\UnitedNationsCountryType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
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
            ->add('nbOfProxiesProposed', ChoiceType::class, [
                'expanded' => true,
                'choices' => [
                    '1' => 1,
                    '2' => 2,
                    '3' => 3,
                ],
            ])
            ->add('electionRounds', ElectionRoundsChoiceType::class, [
                'election_context' => $options['election_context'],
            ])
            ->add('inviteSourceName', TextType::class, [
                'required' => false,
            ])
            ->add('inviteSourceFirstName', TextType::class, [
                'required' => false,
            ])
            ->add('conditions', CheckboxType::class, [
                'required' => false,
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

        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
            $data = $event->getData();

            if (2 === (int) $data['nbOfProxiesProposed'] && 'FR' === $data['country']) {
                $event->getForm()->get('nbOfProxiesProposed')->addError(
                    new FormError(
                    'Attention, vous ne pouvez être mandataire que pour une seule procuration établie en France et une établie à l\'étranger.'
                    )
                );
            }
        });
    }

    public function getBlockPrefix()
    {
        return 'app_procuration_proposal';
    }
}
