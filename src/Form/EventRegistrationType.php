<?php

namespace AppBundle\Form;

use AppBundle\Event\EventRegistrationCommand;
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
            ])
            ->add('lastName', TextType::class)
            ->add('emailAddress', EmailType::class)
            ->add('acceptTerms', CheckboxType::class, [
                'mapped' => false,
            ])
        ;

        /** @var EventRegistrationCommand $registration */
        $registration = $builder->getData();
        if (!$this->authorizationChecker->isGranted('IS_AUTHENTICATED_FULLY')
                || ($builder->getData() instanceof EventRegistrationCommand && !$registration->isNewsletterSubscriber())) {
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
