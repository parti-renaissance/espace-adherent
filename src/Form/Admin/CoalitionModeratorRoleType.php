<?php

namespace App\Form\Admin;

use App\Entity\Coalition\CoalitionModeratorRoleAssociation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CoalitionModeratorRoleType extends AbstractType implements DataTransformerInterface
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('hasRole', CheckboxType::class, [
                'label' => 'Responsable des coalitions',
                'required' => false,
            ])
            ->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) {
                /** @var CoalitionModeratorRoleAssociation $data */
                $data = $event->getData();

                if ($data instanceof CoalitionModeratorRoleAssociation) {
                    $event->setData(null);
                }
            })
            ->addModelTransformer($this)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => CoalitionModeratorRoleAssociation::class,
        ]);
    }

    public function transform($value)
    {
        return $value instanceof CoalitionModeratorRoleAssociation ? true : false;
    }

    public function reverseTransform($value)
    {
        return !empty($value) ? $this->zoneRepository->find($value) : $value;
    }
}
