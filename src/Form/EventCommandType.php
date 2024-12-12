<?php

namespace App\Form;

use App\Entity\Event\EventCategory;
use App\Entity\Event\EventGroupCategory;
use App\Event\EventCommand;
use App\Repository\EventCategoryRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EventCommandType extends AbstractType
{
    public function getParent(): string
    {
        return BaseEventCommandType::class;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('category', EntityType::class, [
                'class' => $options['event_category_class'],
                'choice_label' => 'name',
                'query_builder' => function (EventCategoryRepository $ecr) use ($options) {
                    return $ecr->createQueryForAllEnabledOrderedByName($options['event_group_category']);
                },
                'group_by' => function ($category) {
                    /** @var EventCategory $category */
                    return $category->getEventGroupCategory()->getName();
                },
            ])
        ;

        if (isset($options['extra_fields']) && $options['extra_fields']) {
            $builder
                ->add('private', CheckboxType::class, [
                    'required' => false,
                ])
            ;
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'data_class' => EventCommand::class,
                'event_group_category' => null,
                'extra_fields' => true,
            ])
            ->setAllowedTypes('event_group_category', ['null', EventGroupCategory::class])
            ->setAllowedTypes('extra_fields', 'bool')
        ;
    }
}
