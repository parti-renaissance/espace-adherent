<?php

namespace App\Form\Admin;

use App\Entity\ManagedArea\ManagedArea;
use App\Entity\ReferentTag;
use App\Repository\ReferentTagRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ManagedAreaType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('referentTags', EntityType::class, [
                'label' => false,
                'class' => ReferentTag::class,
                'required' => false,
                'multiple' => true,
                'query_builder' => function (ReferentTagRepository $repository) {
                    return $repository->createSelectDepartmentsQueryBuilder();
                },
            ])
        ;

        $builder
            ->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) {
                /** @var ManagedArea $data */
                $data = $event->getData();

                if ($data instanceof ManagedArea && $data->getReferentTags()->isEmpty()) {
                    $event->setData(null);
                }
            })
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'required' => false,
            'data_class' => ManagedArea::class,
        ]);
    }
}
