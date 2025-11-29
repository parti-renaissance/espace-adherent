<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Event\EventCategory;
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
                    ->orderBy('e.name', 'ASC')
                    ->setParameter('status', EventCategory::ENABLED)
                ;
            },
        ]);
    }

    public function getParent(): string
    {
        return EntityType::class;
    }
}
