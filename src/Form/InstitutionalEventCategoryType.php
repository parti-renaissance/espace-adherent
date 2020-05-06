<?php

namespace App\Form;

use App\Entity\InstitutionalEventCategory;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class InstitutionalEventCategoryType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'class' => InstitutionalEventCategory::class,
            'choice_label' => 'name',
            'query_builder' => function (EntityRepository $er) {
                return $er->createQueryBuilder('institutionalEventCategory')
                    ->where('institutionalEventCategory.status = :status')
                    ->setParameter('status', InstitutionalEventCategory::ENABLED)
                    ->orderBy('institutionalEventCategory.name', 'ASC')
                ;
            },
        ]);
    }

    public function getParent()
    {
        return EntityType::class;
    }
}
