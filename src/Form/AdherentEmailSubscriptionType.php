<?php

namespace App\Form;

use App\Entity\Adherent;
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
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'data_class' => Adherent::class,
                'is_adherent' => true,
            ])
            ->setAllowedTypes('is_adherent', 'bool')
        ;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('subscriptionTypes', EntityType::class, [
                'class' => SubscriptionType::class,
                'choice_label' => 'label',
                'label' => false,
                'expanded' => true,
                'multiple' => true,
                'error_bubbling' => true,
                'query_builder' => function (EntityRepository $er) {
                    return $er
                        ->createQueryBuilder('st')
                        ->orderBy('st.position')
                        ->where('st.code IN (:codes)')
                        ->setParameter('codes', SubscriptionTypeEnum::ALL())
                    ;
                },
                'group_by' => function (SubscriptionType $type) {
                    switch ($type->getCode()) {
                        case SubscriptionTypeEnum::MILITANT_ACTION_SMS:
                            return 'subscription_type.group.communication_mobile';
                        case SubscriptionTypeEnum::MOVEMENT_INFORMATION_EMAIL:
                        case SubscriptionTypeEnum::WEEKLY_LETTER_EMAIL:
                        case SubscriptionTypeEnum::JAM_EMAIL:
                            return 'subscription_type.group.communication_emails';
                        case SubscriptionTypeEnum::DEPUTY_EMAIL:
                        case SubscriptionTypeEnum::REFERENT_EMAIL:
                        case SubscriptionTypeEnum::LOCAL_HOST_EMAIL:
                        case SubscriptionTypeEnum::CANDIDATE_EMAIL:
                        case SubscriptionTypeEnum::SENATOR_EMAIL:
                        case SubscriptionTypeEnum::EVENT_EMAIL:
                            return 'subscription_type.group.territories_emails';
                    }

                    return '';
                },
            ])
            ->add('submit', SubmitType::class, ['label' => 'Enregistrer les modifications'])
        ;
    }

    public function finishView(FormView $view, FormInterface $form, array $options): void
    {
        $subscriptionTypes = $view->children['subscriptionTypes']->vars['choices'];

        $view->children['subscriptionTypes']->vars['choices'] = array_merge([
            'subscription_type.group.communication_mobile' => null,
            'subscription_type.group.communication_emails' => null,
            'subscription_type.group.territories_emails' => null,
        ], $subscriptionTypes);
    }
}
