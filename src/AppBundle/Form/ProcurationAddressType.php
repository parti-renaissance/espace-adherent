<?php

namespace AppBundle\Form;
use AppBundle\Procuration\ProcurationRequestCommand;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProcurationAddressType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('country', UnitedNationsCountryType::class)
            ->add('postalCode', TextType::class)
            ->add('city', HiddenType::class, [
                'required' => false,
                'error_bubbling' => true,
            ])
            ->add('cityName', TextType::class, [
                'required' => false,
            ])
            ->add('address', TextType::class)
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
            'validation_groups' => ['vote', 'address'],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'app_procuration_address';
    }
}
