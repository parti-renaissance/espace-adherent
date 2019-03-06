<?php

namespace AppBundle\Form;

use AppBundle\Entity\EventCategory;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EventCategoryType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'class' => EventCategory::class,
            'choice_label' => 'name',
            'query_builder' => function (EntityRepository $er) {
                return $er->createQueryBuilder('e')
                    ->where('e.status = :status')
                    ->orderBy('e.eventGroupCategory', 'ASC')
                    ->addOrderBy('e.name', 'ASC')
                    ->setParameter('status', EventCategory::ENABLED)
                ;
            },
            'group_by' => function($category) {
                /**  @var EventCategory $category */
                if ($category->getEventGroupCategory()) {
                    return $category->getEventGroupCategory()->getName();
                }
                return null;
            }
        ]);
    }

    public function getParent(): string
    {
        return EntityType::class;
    }
}
