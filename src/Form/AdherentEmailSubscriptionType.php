<?php

namespace App\Form;

use App\Entity\SubscriptionType;
use App\Subscription\SubscriptionTypeEnum;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
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
                    return $er
                        ->createQueryBuilder('st')
                        ->orderBy('st.position')
                        ->where('st.code IN (:codes)')
                        ->setParameter('codes', $options['is_adherent'] ? SubscriptionTypeEnum::ADHERENT_TYPES : SubscriptionTypeEnum::USER_TYPES)
                    ;
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
                        case SubscriptionTypeEnum::SENATOR_EMAIL:
                            return 'subscription_type.group.territories_emails';
                        case SubscriptionTypeEnum::CITIZEN_PROJECT_HOST_EMAIL:
                            return 'subscription_type.group.citizen_project_emails';
                    }

                    return null;
                },
            ])
            ->add('submit', SubmitType::class, ['label' => 'Enregistrer les modifications'])
        ;
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
