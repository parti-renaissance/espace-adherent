<?php

namespace AppBundle\Form;
use AppBundle\Procuration\ProcurationRequestCommand;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProcurationVoteType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('voteCountry', UnitedNationsCountryType::class)
            ->add('votePostalCode', TextType::class)
            ->add('voteCity', HiddenType::class, [
                'required' => false,
                'error_bubbling' => true,
            ])
            ->add('voteCityName', TextType::class, [
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ProcurationRequestCommand::class,
            'validation_groups' => 'vote',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'app_procuration_vote';
    }
}
