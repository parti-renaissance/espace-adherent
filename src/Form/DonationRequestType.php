<?php

namespace App\Form;

use App\Address\Address;
use App\Donation\DonationRequest;
use App\Donation\DonationRequestUtils;
use App\Donation\PayboxPaymentSubscription;
use App\Entity\Adherent;
use App\Form\DataTransformer\FloatToStringTransformer;
use App\Membership\MembershipRegistrationProcess;
use App\Repository\AdherentRepository;
use App\ValueObject\Genders;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Validator\Constraints as Assert;

class DonationRequestType extends AbstractType
{
    public const CONFIRM_DONATION_TYPE_SUBSCRIPTION = 'confirm_subscription_donation';
    public const CONFIRM_DONATION_TYPE_UNIQUE = 'confirm_unique_donation';

    public const CONFIRM_DONATION_TYPE_CHOICES = [
        self::CONFIRM_DONATION_TYPE_SUBSCRIPTION,
        self::CONFIRM_DONATION_TYPE_UNIQUE,
    ];

    private $donationRequestUtils;
    private $membershipRegistrationProcess;
    private $tokenStorage;
    private $requestStack;
    private $adherentRepository;

    public function __construct(
        DonationRequestUtils $donationRequestUtils,
        MembershipRegistrationProcess $membershipRegistrationProcess,
        RequestStack $requestStack,
        TokenStorageInterface $tokenStorage,
        AdherentRepository $adherentRepository
    ) {
        $this->membershipRegistrationProcess = $membershipRegistrationProcess;
        $this->donationRequestUtils = $donationRequestUtils;
        $this->requestStack = $requestStack;
        $this->tokenStorage = $tokenStorage;
        $this->adherentRepository = $adherentRepository;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                $builder
                    ->create('amount', HiddenType::class)
                    ->addViewTransformer(new FloatToStringTransformer())
            )
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
                    'filter_emojis' => true,
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
                })
            ;
        }

        $builder
            ->add('gender', ChoiceType::class, [
                'choices' => Genders::CIVILITY_CHOICES,
                'translation_domain' => 'messages',
                'expanded' => true,
            ])
            ->add('firstName', TextType::class, [
                'filter_emojis' => true,
            ])
            ->add('lastName', TextType::class, [
                'filter_emojis' => true,
            ])
            ->add('emailAddress', EmailType::class)
            ->add('nationality', CountryType::class, [
                'preferred_choices' => [Address::FRANCE],
                'placeholder' => 'Nationalité',
            ])
            ->add('address', TextType::class)
            ->add('postalCode', TextType::class)
            ->add('cityName', TextType::class, [
                'required' => false,
            ])
            ->add('country', UnitedNationsCountryType::class, [
                'preferred_choices' => [Address::FRANCE],
            ])
            ->add('code', IntegerType::class, [
                'required' => false,
            ])
            ->add('isPhysicalPerson', CheckboxType::class, [
                'mapped' => false,
                'constraints' => [new Assert\IsTrue()],
            ])
            ->add('hasFrenchNationality', CheckboxType::class, [
                'mapped' => false,
                'constraints' => [new Assert\IsTrue()],
            ])
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
            $user = $this->tokenStorage->getToken()->getUser();

            if (!$user instanceof Adherent) {
                $user = null;
            }

            // The user comes from the registration process
            if (null === $user && $uuid = $this->membershipRegistrationProcess->getAdherentUuid()) {
                $user = $this->adherentRepository->findByUuid($uuid);
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

    public function getBlockPrefix()
    {
        return 'app_donation';
    }
}
