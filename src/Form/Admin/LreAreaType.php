<?php

namespace App\Form\Admin;

use App\Entity\LreArea;
use App\Entity\ReferentTag;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LreAreaType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('referentTag', EntityType::class, [
                'required' => false,
                'label' => false,
                'class' => ReferentTag::class,
            ])
            ->add('allTags', CheckboxType::class, [
                'label' => 'La République Ensemble sans restriction de zone',
            ])
            ->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) {
                /** @var LreArea $data */
                $data = $event->getData();

                if ($data instanceof LreArea && !$data->getReferentTag() && !$data->isAllTags()) {
                    $event->setData(null);
                }
            })
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => LreArea::class,
        ]);
    }
}
