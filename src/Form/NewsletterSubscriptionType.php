<?php

namespace App\Form;

use App\Entity\NewsletterSubscription;
use App\Repository\NewsletterSubscriptionRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class NewsletterSubscriptionType extends AbstractType
{
    private $newsletterSubscriptionRepository;

    public function __construct(NewsletterSubscriptionRepository $newsletterSubscriptionRepository)
    {
        $this->newsletterSubscriptionRepository = $newsletterSubscriptionRepository;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email', EmailType::class, [
                'required' => true,
            ])
            ->add('postalCode', TextType::class, [
                'required' => false,
            ])
            ->add('country', UnitedNationsCountryType::class, [
                'required' => false,
                'preferred_choices' => ['FR'],
            ])
            ->add('personalDataCollection', AcceptPersonalDataCollectType::class, [
                'mapped' => true,
            ])
        ;

        $builder->addModelTransformer(new CallbackTransformer(
            function ($data) {
                return $data;
            },
            function ($subscription) {
                if ($subscription instanceof NewsletterSubscription) {
                    $existingSubscription = $this->newsletterSubscriptionRepository->findOneNotConfirmedByEmail($subscription->getEmail());
                    if ($existingSubscription) {
                        $existingSubscription->setCountry($subscription->getCountry());
                        $existingSubscription->setPostalCode($subscription->getPostalCode());
                        $existingSubscription->setRecaptcha($subscription->getRecaptcha());

                        return $existingSubscription;
                    }
                }

                return $subscription;
            }
        ));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => NewsletterSubscription::class,
            'csrf_protection' => false,
            'validation_groups' => ['Default', 'Subscription'],
        ]);
    }

    public function getBlockPrefix()
    {
        return 'app_newsletter_subscription';
    }
}
