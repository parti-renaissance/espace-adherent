<?php

namespace App\Form\AdherentMessage;

use App\Entity\AdherentMessage\Filter\MessageFilter;
use App\Entity\Geo\Zone;
use App\Form\CommitteeChoiceType;
use App\Form\EventListener\IncludeExcludeFilterRoleListener;
use App\Form\FilterRoleType;
use App\Form\MemberInterestsChoiceType;
use App\Form\ZoneAutoCompleteType;
use App\Repository\CommitteeRepository;
use App\Validator\ManagedZone;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AdvancedMessageFilterType extends AbstractType
{
    private IncludeExcludeFilterRoleListener $includeExcludeFilterRoleListener;

    public function __construct(IncludeExcludeFilterRoleListener $includeExcludeFilterRoleListener)
    {
        $this->includeExcludeFilterRoleListener = $includeExcludeFilterRoleListener;
    }

    public function getParent(): string
    {
        return SimpleMessageFilterType::class;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('includeAdherentsNoCommittee', CheckboxType::class, ['required' => false])
            ->add('includeAdherentsInCommittee', CheckboxType::class, ['required' => false])
            ->add('includeRoles', FilterRoleType::class, ['required' => false])
            ->add('excludeRoles', FilterRoleType::class, ['required' => false])
            ->add('interests', MemberInterestsChoiceType::class, ['required' => false, 'expanded' => false])
            ->add('committee', CommitteeChoiceType::class, [
                'required' => false,
                'query_builder' => static function (CommitteeRepository $repository) use ($options) {
                    return $repository->getQueryBuilderForZones($options['zones']);
                },
            ])
            ->add('zones', ZoneAutoCompleteType::class, [
                'multiple' => true,
                'remote_params' => [
                    'space_type' => $options['message_type'],
                    'active_only' => true,
                    'types' => [
                        Zone::BOROUGH,
                        Zone::CITY,
                        Zone::DEPARTMENT,
                        Zone::COUNTRY,
                        Zone::DISTRICT,
                    ],
                ],
                'constraints' => [
                    new ManagedZone($options['message_type']),
                ],
            ])
        ;

        $builder->addEventSubscriber($this->includeExcludeFilterRoleListener);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'data_class' => MessageFilter::class,
                'zones' => null,
                'message_type' => null,
            ])
            ->setAllowedTypes('zones', ['array'])
            ->setAllowedTypes('message_type', ['string'])
            ->setRequired(['zones', 'message_type'])
        ;
    }
}
