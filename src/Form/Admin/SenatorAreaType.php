<?php

namespace App\Form\Admin;

use App\Entity\ReferentTag;
use App\Entity\SenatorArea;
use App\Repository\ReferentTagRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SenatorAreaType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('departmentTag', EntityType::class, [
                'required' => false,
                'label' => false,
                'class' => ReferentTag::class,
                'query_builder' => function (ReferentTagRepository $repository) {
                    return $repository->createSelectSenatorAreaQueryBuilder();
                },
            ])
            ->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) {
                /** @var SenatorArea $data */
                $data = $event->getData();

                if ($data instanceof SenatorArea && !$data->getDepartmentTag()) {
                    $event->setData(null);
                }
            })
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => SenatorArea::class,
        ]);
    }
}
