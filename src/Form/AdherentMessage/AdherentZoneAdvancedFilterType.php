<?php

namespace App\Form\AdherentMessage;

use App\Entity\AdherentMessage\Filter\AdherentGeoZoneFilter;
use App\Form\CommitteeChoiceType;
use App\Form\DatePickerType;
use App\Form\EventListener\IncludeExcludeFilterRoleListener;
use App\Form\FilterRoleType;
use App\Form\GenderType;
use App\Form\MemberInterestsChoiceType;
use App\Repository\CommitteeRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AdherentZoneAdvancedFilterType extends AbstractType
{
    /** @var IncludeExcludeFilterRoleListener */
    private $includeExcludeFilterRoleListener;

    public function __construct(IncludeExcludeFilterRoleListener $includeExcludeFilterRoleListener)
    {
        $this->includeExcludeFilterRoleListener = $includeExcludeFilterRoleListener;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('includeAdherentsNoCommittee', CheckboxType::class, ['required' => false])
            ->add('includeAdherentsInCommittee', CheckboxType::class, ['required' => false])
            ->add('includeRoles', FilterRoleType::class, ['required' => false])
            ->add('excludeRoles', FilterRoleType::class, ['required' => false])
            ->add('gender', GenderType::class, [
                'placeholder' => 'Tous',
                'expanded' => true,
                'required' => false,
            ])
            ->add('ageMin', IntegerType::class, ['required' => false])
            ->add('ageMax', IntegerType::class, ['required' => false])
            ->add('firstName', TextType::class, ['required' => false])
            ->add('lastName', TextType::class, ['required' => false])
            ->add('city', TextType::class, ['required' => false])
            ->add('interests', MemberInterestsChoiceType::class, ['required' => false, 'expanded' => false])
            ->add('registeredSince', DatePickerType::class, ['required' => false])
            ->add('registeredUntil', DatePickerType::class, ['required' => false])
            ->add('committee', CommitteeChoiceType::class, [
                'required' => false,
                'query_builder' => static function (CommitteeRepository $repository) use ($options) {
                    return $repository->createQueryBuilderForZones($options['zones']);
                },
            ])
        ;

        $builder->addEventSubscriber($this->includeExcludeFilterRoleListener);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class' => AdherentGeoZoneFilter::class,
                'zones' => null,
            ])
            ->setAllowedTypes('zones', ['array'])
            ->setRequired(['zones'])
        ;
    }
}
