<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\DataTransformer\DateTimeToArrayTransformer;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MonthChoiceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->remove('day')
            ->resetViewTransformers()
            ->addViewTransformer(new DateTimeToArrayTransformer(
                $options['model_timezone'], $options['view_timezone'], ['year', 'month']
            ))
        ;

        if ($options['pre_set_now']) {
            $builder->addEventListener(FormEvents::PRE_SET_DATA, [$this, 'preSetNow']);
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'years' => range(date('Y') - 100, date('Y')),
                'pre_set_now' => false,
            ])
            ->setAllowedTypes('pre_set_now', 'bool')
        ;
    }

    public function getParent()
    {
        return DateType::class;
    }

    public function preSetNow(FormEvent $event)
    {
        if (null === $event->getData()) {
            $event->setData(new \DateTimeImmutable());
        }
    }
}
