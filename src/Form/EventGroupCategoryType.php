<?php

namespace App\Form;

use App\Entity\EventCategory;
use App\Repository\EventCategoryRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EventGroupCategoryType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'class' => EventCategory::class,
            'choice_label' => 'name',
            'query_builder' => function (EventCategoryRepository $ecr) {
                return $ecr->createQueryForAllEnabledOrderedByName();
            },
            'group_by' => function ($category) {
                /** @var EventCategory $category */
                return $category->getEventGroupCategory()->getName();
            },
        ]);
    }

    public function getParent()
    {
        return EntityType::class;
    }
}
