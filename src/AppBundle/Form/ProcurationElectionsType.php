<?php

namespace AppBundle\Form;
use AppBundle\Procuration\ProcurationRequestCommand;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProcurationElectionsType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('electionPresidentialFirstRound', CheckboxType::class, [
                'required' => false,
            ])
            ->add('electionPresidentialSecondRound', CheckboxType::class, [
                'required' => false,
            ])
            ->add('electionLegislativeFirstRound', CheckboxType::class, [
                'required' => false,
            ])
            ->add('electionLegislativeSecondRound', CheckboxType::class, [
                'required' => false,
            ])
            ->add('country', HiddenType::class)
            ->add('postalCode', HiddenType::class)
            ->add('city', HiddenType::class)
            ->add('cityName', HiddenType::class)
            ->add('address', HiddenType::class)
            ->add('voteCountry', HiddenType::class)
            ->add('votePostalCode', HiddenType::class)
            ->add('voteCity', HiddenType::class)
            ->add('voteCityName', HiddenType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ProcurationRequestCommand::class,
            'validation_groups' => ['vote', 'address', 'elections'],
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
