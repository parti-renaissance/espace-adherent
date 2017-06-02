<?php

namespace AppBundle\Form\TypeExtension;

use AppBundle\Utils\EmojisRemover;
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
        if ($options['filter_emojis']) {
            $builder->addEventListener(FormEvents::PRE_SUBMIT, [$this, 'filterEmojisOnPreSubmit'], 10);
        }

        if ($options['purify_html']) {
            $builder->addEventListener(FormEvents::SUBMIT, [$this, 'purifyOnSubmit']);
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'empty_data' => '',
            'purify_html' => false,
            'filter_emojis' => false,
        ]);

        $resolver
            ->setAllowedTypes('purify_html', 'bool')
            ->setAllowedTypes('filter_emojis', 'bool')
        ;
    }

    public function getExtendedType()
    {
        return TextType::class;
    }

    public function filterEmojisOnPreSubmit(FormEvent $event)
    {
        if ($data = $event->getData()) {
            $event->setData(EmojisRemover::remove($data));
        }
    }

    public function purifyOnSubmit(FormEvent $event)
    {
        if ($data = $event->getData()) {
            $event->setData(HtmlPurifier::purify($data));
        }
    }
}
