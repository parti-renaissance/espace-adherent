<?php

namespace App\Form\Admin;

use App\Entity\Geo\Zone;
use App\Entity\ManagedArea\CandidateManagedArea;
use App\Repository\Geo\ZoneRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CandidateManagedAreaType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('zone', EntityType::class, [
                'label' => false,
                'required' => false,
                'class' => Zone::class,
                'query_builder' => function (ZoneRepository $repository) {
                    return $repository->createSelectForCandidatesQueryBuilder();
                },
            ])
        ;

        $builder
            ->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) {
                /** @var CandidateManagedArea $data */
                $data = $event->getData();

                if ($data instanceof CandidateManagedArea && !$data->getZone()) {
                    $event->setData(null);
                }
            })
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'required' => false,
            'data_class' => CandidateManagedArea::class,
        ]);
    }
}
