<?php

namespace App\Form\ApplicationRequest;

use App\Entity\ApplicationRequest\ApplicationRequestTag;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TagType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'label' => 'Tag de candidature',
            'class' => ApplicationRequestTag::class,
            'query_builder' => static function (EntityRepository $entityRepository) {
                return $entityRepository->createQueryBuilder('art')->orderBy('art.name');
            },
            'multiple' => true,
            'required' => false,
        ]);
    }

    public function getParent()
    {
        return EntityType::class;
    }
}
