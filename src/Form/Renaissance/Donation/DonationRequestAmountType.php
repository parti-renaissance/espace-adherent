<?php

namespace App\Form\Renaissance\Donation;

use App\Donation\DonationRequest;
use App\Donation\PayboxPaymentSubscription;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DonationRequestAmountType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('montant', HiddenType::class, [
                'mapped' => false,
            ])
            ->add('abonnement', HiddenType::class, [
                'mapped' => false,
            ])
        ;

        $builder->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) {
            $form = $event->getForm();

            if ($donationRequest = $event->getData()) {
                $donationRequest->setAmount((float) $form->get('montant')->getData());
                $donationRequest->setDuration($form->get('abonnement')->getData() ? PayboxPaymentSubscription::UNLIMITED : PayboxPaymentSubscription::NONE);
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class' => DonationRequest::class,
                'validation_groups' => ['choose_donation_amount'],
                'csrf_protection' => false,
            ])
        ;
    }

    public function getBlockPrefix()
    {
        return '';
    }
}
