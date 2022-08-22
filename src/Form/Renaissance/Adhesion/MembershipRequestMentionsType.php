<?php

namespace App\Form\Renaissance\Adhesion;

use App\Renaissance\Membership\MembershipRequestCommand;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MembershipRequestMentionsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('conditions', CheckboxType::class, [
                'required' => false,
            ])
            ->add('allowEmailNotifications', CheckboxType::class, [
                'required' => false,
            ])
            ->add('allowMobileNotifications', CheckboxType::class, [
                'required' => false,
            ])
        ;

        $builder->add('membership_request_mentions', SubmitType::class, ['label' => 'Étape suivante']);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class' => MembershipRequestCommand::class,
                'validation_groups' => 'membership_request_mentions',
            ])
        ;
    }

    public function getBlockPrefix()
    {
        return 'app_renaissance_membership';
    }
}
