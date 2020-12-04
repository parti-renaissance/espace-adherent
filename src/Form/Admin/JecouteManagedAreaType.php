<?php

namespace App\Form\Admin;

use App\Entity\Geo\Zone;
use App\Entity\JecouteManagedArea;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class JecouteManagedAreaType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('zone', EntityType::class, [
                'required' => false,
                'label' => false,
                'class' => Zone::class,
                'query_builder' => function (EntityRepository $er) {
                    $qb = $er->createQueryBuilder('zone');

                    return $qb
                        ->where($qb->expr()->orX(
                            $qb->expr()->in('zone.type', ':types'),
                            'zone.type = :borough AND zone.name LIKE :paris',
                            'zone.type = :region AND zone.name = :corse'
                        ))
                        ->setParameters([
                            'types' => [Zone::DEPARTMENT, Zone::FOREIGN_DISTRICT],
                            'borough' => Zone::BOROUGH,
                            'region' => Zone::REGION,
                            'paris' => 'Paris %',
                            'corse' => 'Corse',
                        ])
                    ;
                },
            ])
            ->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) {
                /** @var JecouteManagedArea $data */
                $data = $event->getData();

                if ($data instanceof JecouteManagedArea && !$data->getZone()) {
                    $event->setData(null);
                }
            })
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => JecouteManagedArea::class,
        ]);
    }
}
