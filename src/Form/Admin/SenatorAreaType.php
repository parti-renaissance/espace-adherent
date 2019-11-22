<?php

namespace AppBundle\Form\Admin;

use AppBundle\Entity\ReferentTag;
use AppBundle\Entity\SenatorArea;
use Doctrine\ORM\EntityRepository;
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
                'query_builder' => function (EntityRepository $repository) {
                    return $repository->createQueryBuilder('tag')
                        ->where('tag.type = :type')
                        ->setParameter('type', ReferentTag::TYPE_DEPARTMENT)
                    ;
                },
            ])
            ->add('entireWorld', null, [
                'label' => 'Le monde entier',
                'required' => false,
            ])
            ->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) {
                /** @var SenatorArea $data */
                $data = $event->getData();

                if ($data instanceof SenatorArea && !$data->getDepartmentTag() && !$data->isEntireWorld()) {
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
