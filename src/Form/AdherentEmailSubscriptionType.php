<?php

namespace AppBundle\Form;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\SubscriptionType;
use AppBundle\Membership\CitizenProjectNotificationDistance;
use AppBundle\Subscription\SubscriptionTypeEnum;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AdherentEmailSubscriptionType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'is_adherent' => true,
            ])
            ->setAllowedTypes('is_adherent', 'bool')
        ;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('subscriptionTypes', EntityType::class, [
                'class' => SubscriptionType::class,
                'choice_label' => 'label',
                'label' => false,
                'expanded' => true,
                'multiple' => true,
                'error_bubbling' => true,
                'query_builder' => function (EntityRepository $er) use ($options) {
                    $eq = $er
                        ->createQueryBuilder('st')
                        ->orderBy('st.position')
                    ;

                    if (!$options['is_adherent']) {
                        $eq
                            ->where('st.code IN (:codes)')
                            ->setParameter('codes', SubscriptionTypeEnum::USER_TYPES)
                        ;
                    }

                    return $eq;
                },
                'group_by' => function (SubscriptionType $type) {
                    switch ($type->getCode()) {
                        case SubscriptionTypeEnum::MILITANT_ACTION_SMS:
                            return 'subscription_type.group.communication_mobile';
                        case SubscriptionTypeEnum::MOVEMENT_INFORMATION_EMAIL:
                        case SubscriptionTypeEnum::WEEKLY_LETTER_EMAIL:
                            return 'subscription_type.group.communication_emails';
                        case SubscriptionTypeEnum::DEPUTY_EMAIL:
                        case SubscriptionTypeEnum::REFERENT_EMAIL:
                        case SubscriptionTypeEnum::LOCAL_HOST_EMAIL:
                        case SubscriptionTypeEnum::MUNICIPAL_EMAIL:
                            return 'subscription_type.group.territories_emails';
                        case SubscriptionTypeEnum::CITIZEN_PROJECT_HOST_EMAIL:
                        case SubscriptionTypeEnum::CITIZEN_PROJECT_CREATION_EMAIL:
                            return 'subscription_type.group.citizen_project_emails';
                    }

                    return null;
                },
            ])
            ->add('citizenProjectCreationEmailSubscriptionRadius', ChoiceType::class, [
                'choices' => CitizenProjectNotificationDistance::DISTANCES,
                'label' => false,
                'attr' => [
                    'style' => 'display: none;',
                ],
                'error_bubbling' => true,
            ])
            ->add('submit', SubmitType::class, ['label' => 'Enregistrer les modifications'])
        ;

        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
            $data = $event->getData();
            if (empty($data['citizenProjectCreationEmailSubscriptionRadius'])
                || Adherent::DISABLED_CITIZEN_PROJECT_EMAIL == $data['citizenProjectCreationEmailSubscriptionRadius']
            ) {
                $event->getForm()->add('citizenProjectCreationEmailSubscriptionRadius', ChoiceType::class, [
                    'choices' => array_merge(CitizenProjectNotificationDistance::DISTANCES, ['Désactivé' => Adherent::DISABLED_CITIZEN_PROJECT_EMAIL]),
                ]);
                $data['citizenProjectCreationEmailSubscriptionRadius'] = Adherent::DISABLED_CITIZEN_PROJECT_EMAIL;
                $event->setData($data);
            }
        });
    }

    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        $subscriptionTypes = $view->children['subscriptionTypes']->vars['choices'];

        $view->children['subscriptionTypes']->vars['choices'] = \array_merge([
            'subscription_type.group.communication_mobile' => null,
            'subscription_type.group.communication_emails' => null,
            'subscription_type.group.territories_emails' => null,
            'subscription_type.group.citizen_project_emails' => null,
        ], $subscriptionTypes);
    }
}
