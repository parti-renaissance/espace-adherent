<?php

namespace App\Form;

use App\Address\AddressInterface;
use App\Donation\Paybox\PayboxPaymentSubscription;
use App\Donation\Request\DonationRequest;
use App\Donation\Request\DonationRequestUtils;
use App\Entity\Adherent;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class DonationRequestType extends AbstractType
{
    public const CONFIRM_DONATION_TYPE_SUBSCRIPTION = 'confirm_subscription_donation';
    public const CONFIRM_DONATION_TYPE_UNIQUE = 'confirm_unique_donation';

    public const CONFIRM_DONATION_TYPE_CHOICES = [
        self::CONFIRM_DONATION_TYPE_SUBSCRIPTION,
        self::CONFIRM_DONATION_TYPE_UNIQUE,
    ];

    private $donationRequestUtils;
    private $tokenStorage;
    private $requestStack;

    public function __construct(
        DonationRequestUtils $donationRequestUtils,
        RequestStack $requestStack,
        TokenStorageInterface $tokenStorage,
    ) {
        $this->donationRequestUtils = $donationRequestUtils;
        $this->requestStack = $requestStack;
        $this->tokenStorage = $tokenStorage;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('amount', AmountType::class)
            ->add('duration', HiddenType::class)
        ;

        $request = $this->requestStack->getCurrentRequest();

        if ($this->donationRequestUtils->hasAmountAlert(
            $request->get('montant'), $request->query->getInt('abonnement'))
        ) {
            $builder
                ->add('confirmDonationType', ChoiceType::class, [
                    'expanded' => true,
                    'choices' => self::CONFIRM_DONATION_TYPE_CHOICES,
                ])
                ->add('confirmSubscriptionAmount', TextType::class, [
                    'data' => $this->donationRequestUtils->getDefaultConfirmSubscriptionAmount(
                        $request->get('montant')
                    ),
                    'attr' => ['size' => 2, 'maxlength' => 4],
                ])
                ->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $formEvent) {
                    $form = $formEvent->getForm();

                    if (self::CONFIRM_DONATION_TYPE_SUBSCRIPTION === $form->get('confirmDonationType')->getData()
                        && $donationRequest = $formEvent->getData()
                    ) {
                        $donationRequest->setAmount($form->get('confirmSubscriptionAmount')->getData());
                        $donationRequest->setDuration(PayboxPaymentSubscription::UNLIMITED);
                    }
                }, 10)
            ;
        }

        $builder
            ->add('gender', CivilityType::class)
            ->add('firstName', TextType::class)
            ->add('lastName', TextType::class)
            ->add('emailAddress', EmailType::class)
            ->add('nationality', CountryType::class, [
                'preferred_choices' => [AddressInterface::FRANCE],
                'placeholder' => 'Nationalité',
            ])
            ->add('isPhysicalPerson', RequiredCheckboxType::class)
            ->add('hasFrenchNationality', RequiredCheckboxType::class)
            ->add('personalDataCollection', AcceptPersonalDataCollectType::class)
            ->add('submit', SubmitType::class, [
                'label' => 'Continuer',
            ])
        ;

        $builder->addEventListener(FormEvents::PRE_SET_DATA, [$this, 'createInitialDonationRequest']);
    }

    public function createInitialDonationRequest(FormEvent $formEvent): void
    {
        if (!$formEvent->getData()) {
            $user = $this->tokenStorage->getToken()?->getUser();

            if (!$user instanceof Adherent) {
                $user = null;
            }

            $formEvent->setData($this->donationRequestUtils->createFromRequest(
                $this->requestStack->getCurrentRequest(), $user
            ));
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'locale' => 'fr',
                'data_class' => DonationRequest::class,
                'translation_domain' => false,
            ])
            ->setAllowedTypes('locale', ['null', 'string'])
        ;
    }

    public function getBlockPrefix(): string
    {
        return 'app_donation';
    }
}
