<?php

namespace App\Form\ManagedUsers;

use App\Entity\Geo\Zone;
use App\Form\DatePickerType;
use App\Form\EventListener\IncludeExcludeFilterRoleListener;
use App\Form\FilterRoleType;
use App\Form\MemberInterestsChoiceType;
use App\Form\MyZoneChoiceType;
use App\ManagedUsers\ManagedUsersFilter;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ManagedUsersFilterType extends AbstractManagedUsersFilterType
{
    /** @var IncludeExcludeFilterRoleListener */
    private $includeExcludeFilterRoleListener;

    public function __construct(IncludeExcludeFilterRoleListener $includeExcludeFilterRoleListener)
    {
        $this->includeExcludeFilterRoleListener = $includeExcludeFilterRoleListener;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder
            ->add('includeAdherentsNoCommittee', CheckboxType::class, ['required' => false])
            ->add('includeAdherentsInCommittee', CheckboxType::class, ['required' => false])
            ->add('includeRoles', FilterRoleType::class, ['required' => false])
            ->add('excludeRoles', FilterRoleType::class, ['required' => false])
            ->add('interests', MemberInterestsChoiceType::class, ['required' => false, 'expanded' => false])
            ->add('registeredSince', DatePickerType::class, ['required' => false])
            ->add('registeredUntil', DatePickerType::class, ['required' => false])
            ->add('voteInCommittee', ChoiceType::class, [
                'required' => false,
                'choices' => [
                    'common.all' => null,
                    'global.yes' => true,
                    'global.no' => false,
                ],
                'choice_value' => function ($choice) {
                    return false === $choice ? '0' : (string) $choice;
                },
            ])
        ;

        if (false === $options['single_zone']) {
            $builder->add('managedZones', MyZoneChoiceType::class, [
                'placeholder' => 'Tous',
                'required' => false,
                'by_reference' => false,
            ]);

            $managedZonesField = $builder->get('managedZones');

            $managedZonesField->addModelTransformer(new CallbackTransformer(
                static function ($value) use ($managedZonesField) {
                    if (\is_array($value) && \count($value) === \count($managedZonesField->getOption('choices'))) {
                        return null;
                    }

                    return $value;
                },
                static function ($value) use ($managedZonesField) {
                    if (null === $value) {
                        return  $managedZonesField->getOption('choices');
                    }

                    if ($value instanceof Zone) {
                        return [$value];
                    }

                    return $value;
                },
            ));
        }

        $builder->addEventSubscriber($this->includeExcludeFilterRoleListener);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver
            ->setDefaults([
                'data_class' => ManagedUsersFilter::class,
                'single_zone' => false,
                'allow_extra_fields' => true,
            ])
            ->setAllowedTypes('single_zone', ['bool'])
        ;
    }
}
