<?php

namespace App\Form\Admin;

use App\Entity\ReferentTag;
use App\Entity\SenatorialCandidateManagedArea;
use App\Repository\ReferentTagRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SenatorialCandidateManagedAreaType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('departmentTags', EntityType::class, [
                'label' => false,
                'class' => ReferentTag::class,
                'required' => false,
                'multiple' => true,
                'query_builder' => function (ReferentTagRepository $repository) {
                    return $repository->createSelectSenatorAreaQueryBuilder();
                },
            ])
        ;

        $builder
            ->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) {
                /** @var SenatorialCandidateManagedArea $data */
                $data = $event->getData();

                if ($data instanceof SenatorialCandidateManagedArea && $data->getDepartmentTags()->isEmpty()) {
                    $event->setData(null);
                }
            })
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'required' => false,
            'data_class' => SenatorialCandidateManagedArea::class,
        ]);
    }
}
