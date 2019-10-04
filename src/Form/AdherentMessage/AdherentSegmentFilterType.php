<?php

namespace AppBundle\Form\AdherentMessage;

use AppBundle\AdherentSegment\AdherentSegmentTypeEnum;
use AppBundle\Entity\AdherentMessage\Filter\AdherentSegmentAwareFilterInterface;
use AppBundle\Form\AdherentSegmentType;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Security;

class AdherentSegmentFilterType extends AbstractType
{
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('adherentSegment', AdherentSegmentType::class, [
            'required' => false,
            'query_builder' => function (EntityRepository $repository) use ($options) {
                return $repository->createQueryBuilder('segment')
                    ->where('segment.author = :author')
                    ->andWhere('segment.segmentType = :type')
                    ->setParameters([
                        'author' => $this->security->getUser(),
                        'type' => $options['segment_type'],
                    ])
                ;
            },
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class' => AdherentSegmentAwareFilterInterface::class,
                'segment_type' => null,
            ])
            ->setAllowedValues('segment_type', AdherentSegmentTypeEnum::toArray())
        ;
    }
}
