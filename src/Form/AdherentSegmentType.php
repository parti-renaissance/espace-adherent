<?php

namespace AppBundle\Form;

use AppBundle\Entity\AdherentSegment;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Security;

class AdherentSegmentType extends AbstractType
{
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'placeholder' => 'Choisissez une liste de diffusion',
            'class' => AdherentSegment::class,
            'choice_value' => 'uuid',
            'choice_label' => static function (AdherentSegment $segment) {
                if (!$segment->isSynchronized()) {
                    return $segment->getLabel().' (indisponible, en cours de préparation...)';
                }

                return $segment->getLabel();
            },
            'query_builder' => function (EntityRepository $repository) {
                return $repository->createQueryBuilder('segment')
                    ->where('segment.author = :author')
                    ->setParameter('author', $this->security->getUser())
                ;
            },
            'choice_attr' => static function (AdherentSegment $segment) {
                if (!$segment->isSynchronized()) {
                    return ['disabled' => 'disabled'];
                }

                return [];
            },
        ]);
    }

    public function getParent()
    {
        return EntityType::class;
    }
}
