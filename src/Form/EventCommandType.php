<?php

namespace App\Form;

use App\Entity\Event\EventCategory;
use App\Entity\Event\EventGroupCategory;
use App\Event\EventCommand;
use App\Repository\EventCategoryRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EventCommandType extends AbstractType
{
    public function getParent()
    {
        return BaseEventCommandType::class;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
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
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class' => EventCommand::class,
                'event_group_category' => null,
            ])
            ->setAllowedTypes('event_group_category', ['null', EventGroupCategory::class])
        ;
    }
}
