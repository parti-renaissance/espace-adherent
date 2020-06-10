<?php

namespace App\Form;

use App\Event\EventRegistrationCommand;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class EventRegistrationType extends AbstractType
{
    private $authorizationChecker;

    public function __construct(AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->authorizationChecker = $authorizationChecker;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('firstName', TextType::class, [
                'filter_emojis' => true,
                'format_identity_case' => true,
            ])
            ->add('lastName', TextType::class, [
                'format_identity_case' => true,
            ])
            ->add('emailAddress', EmailType::class)
            ->add('acceptTerms', RequiredCheckboxType::class)
        ;

        $registration = $builder->getData();
        if (!$this->authorizationChecker->isGranted('IS_AUTHENTICATED_FULLY')
                || ($registration instanceof EventRegistrationCommand && !$registration->isNewsletterSubscriber())) {
            $builder
                ->add('newsletterSubscriber', CheckboxType::class, [
                    'required' => false,
                ])
            ;
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => EventRegistrationCommand::class,
        ]);
    }

    public function getBlockPrefix()
    {
        return 'event_registration';
    }
}
