<?php

namespace AppBundle\Form\TypeExtension;

use AppBundle\Utils\HtmlPurifier;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TextTypeExtension extends AbstractTypeExtension
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if ($options['purify_html']) {
            $builder->addEventListener(FormEvents::SUBMIT, [$this, 'purifyOnSubmit']);
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefault('purify_html', false)
            ->setAllowedTypes('purify_html', 'bool')
        ;
    }

    public function getExtendedType()
    {
        return TextType::class;
    }

    public function purifyOnSubmit(FormEvent $event)
    {
        $event->setData(HtmlPurifier::purify($event->getData()));
    }
}
