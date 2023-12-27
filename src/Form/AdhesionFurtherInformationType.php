<?php

namespace App\Form;

use App\Address\AddressInterface;
use App\Entity\Adherent;
use App\Entity\SubscriptionType;
use App\Repository\SubscriptionTypeRepository;
use App\Subscription\SubscriptionTypeEnum;
use Doctrine\ORM\EntityRepository;
use Misd\PhoneNumberBundle\Form\Type\PhoneNumberType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
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
            ->add('refuseJamNotification', CheckboxType::class, ['required' => false, 'mapped' => false])
            ->add('phone', PhoneNumberType::class, [
                'required' => false,
                'widget' => PhoneNumberType::WIDGET_COUNTRY_CHOICE,
                'preferred_country_choices' => [AddressInterface::FRANCE],
                'default_region' => AddressInterface::FRANCE,
                'country_display_type' => PhoneNumberType::DISPLAY_COUNTRY_SHORT,
            ])
            ->add('subscriptionTypes', EntityType::class, [
                'class' => SubscriptionType::class,
                'choice_label' => fn (SubscriptionType $type) => "Je souhaite recevoir les informations sur l'actualité de Renaissance et ses communications politiques par SMS et téléphone",
                'label' => false,
                'expanded' => true,
                'multiple' => true,
                'query_builder' => function (EntityRepository $er) {
                    return $er
                        ->createQueryBuilder('st')
                        ->where('st.code = :code')
                        ->setParameter('code', SubscriptionTypeEnum::MILITANT_ACTION_SMS)
                    ;
                },
            ])
        ;

        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
            /** @var Adherent $adherent */
            $adherent = $event->getData();

            if ($event->getForm()->get('refuseJamNotification')->getData()) {
                $adherent->removeSubscriptionTypeByCode(SubscriptionTypeEnum::JAM_EMAIL);
            } else {
                $adherent->addSubscriptionType($this->subscriptionTypeRepository->findOneByCode(SubscriptionTypeEnum::JAM_EMAIL));
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Adherent::class,
        ]);
    }
}
