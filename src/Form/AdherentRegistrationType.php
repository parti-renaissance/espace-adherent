<?php

namespace AppBundle\Form;

use AppBundle\Membership\MembershipRequest;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AdherentRegistrationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->remove('position')
            ->remove('comMobile')
            ->add('password', PasswordType::class)
            ->add('conditions', CheckboxType::class, [
                'required' => false,
            ])
            ->add('comEmail', CheckboxType::class, [
                'required' => false,
            ])
            ->add('emailAddress', RepeatedType::class, [
                'type' => EmailType::class,
                'invalid_message' => 'common.email.repeated',
            ])
            ->add('address', AddressType::class, [
                'label' => false,
                'child_error_bubbling' => false,
            ])
        ;

        $builder->addEventListener(FormEvents::POST_SET_DATA, function (FormEvent $formEvent) {
            /** @var MembershipRequest $membershipRequest */
            $membershipRequest = $formEvent->getData();
            if ($membershipRequest) {
                $membershipRequest->comMobile = $membershipRequest->comEmail;
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => MembershipRequest::class,
            'validation_groups' => ['Update', 'Conditions', 'Registration'],
        ]);
    }

    public function getParent()
    {
        return AdherentType::class;
    }
}
