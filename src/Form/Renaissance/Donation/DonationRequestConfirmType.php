<?php

namespace App\Form\Renaissance\Donation;

use App\Donation\Paybox\PayboxPaymentSubscription;
use App\Donation\Request\DonationRequest;
use App\Donation\Request\DonationRequestUtils;
use App\Form\AmountType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DonationRequestConfirmType extends AbstractType
{
    public const CONFIRM_DONATION_TYPE_SUBSCRIPTION = 'confirm_subscription_donation';
    public const CONFIRM_DONATION_TYPE_UNIQUE = 'confirm_unique_donation';

    public const CONFIRM_DONATION_TYPE_CHOICES = [
        self::CONFIRM_DONATION_TYPE_SUBSCRIPTION,
        self::CONFIRM_DONATION_TYPE_UNIQUE,
    ];

    private DonationRequestUtils $donationRequestUtils;

    public function __construct(DonationRequestUtils $donationRequestUtils)
    {
        $this->donationRequestUtils = $donationRequestUtils;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('amount', AmountType::class)
            ->add('duration', HiddenType::class)
            ->add('confirmDonationType', ChoiceType::class, [
                'expanded' => true,
                'choices' => self::CONFIRM_DONATION_TYPE_CHOICES,
            ])
            ->add('confirmSubscriptionAmount', TextType::class, [
                'attr' => ['size' => 2, 'maxlength' => 4],
            ])
        ;

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            /** @var DonationRequest $donationRequest */
            $donationRequest = $event->getData();

            $donationRequest->setConfirmSubscriptionAmount($this->donationRequestUtils->getDefaultConfirmSubscriptionAmount((string) $donationRequest->getAmount()));
        });

        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
            $form = $event->getForm();

            if (self::CONFIRM_DONATION_TYPE_SUBSCRIPTION === $form->get('confirmDonationType')->getData()
                && $donationRequest = $event->getData()
            ) {
                $donationRequest->setAmount($form->get('confirmSubscriptionAmount')->getData());
                $donationRequest->setDuration(PayboxPaymentSubscription::UNLIMITED);
            }
        }, 10);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class' => DonationRequest::class,
                'validation_groups' => ['donation_confirm_type'],
            ])
        ;
    }

    public function getBlockPrefix(): string
    {
        return 'app_renaissance_donation';
    }
}
