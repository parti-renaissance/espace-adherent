<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Adherent;
use App\Repository\SubscriptionTypeRepository;
use App\Subscription\SubscriptionTypeEnum;
use Misd\PhoneNumberBundle\Form\Type\PhoneNumberType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AdhesionFurtherInformationType extends AbstractType
{
    public function __construct(private readonly SubscriptionTypeRepository $subscriptionTypeRepository)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('mandates', AdherentMandateType::class, [
                'label' => false,
                'required' => false,
                'multiple' => true,
                'expanded' => true,
            ])
            ->add('birthdate', BirthdateType::class)
            ->add('phone', TelNumberType::class, [
                'required' => false,
                'country_display_type' => PhoneNumberType::DISPLAY_COUNTRY_SHORT,
            ])
            ->add('acceptSmsNotification', CheckboxType::class, ['required' => false, 'mapped' => false])
        ;

        if ($options['with_jam_notification']) {
            $builder->add('refuseJamNotification', CheckboxType::class, ['required' => false, 'mapped' => false]);
        }

        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) use ($options) {
            /** @var Adherent $adherent */
            $adherent = $event->getData();
            $form = $event->getForm();

            if ($options['with_jam_notification']) {
                if ($form->get('refuseJamNotification')->getData()) {
                    $adherent->removeSubscriptionTypeByCode(SubscriptionTypeEnum::JAM_EMAIL);
                } else {
                    $adherent->addSubscriptionType(
                        $this->subscriptionTypeRepository->findOneByCode(SubscriptionTypeEnum::JAM_EMAIL)
                    );
                }
            }

            if ($form->get('acceptSmsNotification')->getData()) {
                $adherent->addSubscriptionType($this->subscriptionTypeRepository->findOneByCode(SubscriptionTypeEnum::MILITANT_ACTION_SMS));
            } else {
                $adherent->removeSubscriptionTypeByCode(SubscriptionTypeEnum::MILITANT_ACTION_SMS);
            }
        }, 10);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'data_class' => Adherent::class,
                'with_jam_notification' => true,
            ])
            ->setAllowedTypes('with_jam_notification', 'bool')
        ;
    }
}
