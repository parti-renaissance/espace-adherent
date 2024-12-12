<?php

namespace App\Form\AdherentMessage;

use App\AdherentSegment\AdherentSegmentTypeEnum;
use App\Entity\AdherentMessage\Filter\AdherentSegmentAwareFilterInterface;
use App\Form\AdherentSegmentType;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AdherentSegmentFilterType extends AbstractType
{
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
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

    public function configureOptions(OptionsResolver $resolver): void
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
