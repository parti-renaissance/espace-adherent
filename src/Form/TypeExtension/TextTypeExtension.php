<?php

namespace AppBundle\Form\TypeExtension;

use AppBundle\Utils\EmojisRemover;
use AppBundle\Utils\HtmlPurifier;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TextTypeExtension extends AbstractTypeExtension
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'empty_data' => '',
                'purify_html' => false,
                'filter_emojis' => false,
                'format_title_case' => false,
                'with_character_count' => false,
            ])
            ->setAllowedTypes('purify_html', 'bool')
            ->setAllowedTypes('filter_emojis', 'bool')
            ->setAllowedTypes('format_title_case', 'bool')
            ->setAllowedTypes('with_character_count', 'bool')
        ;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if ($options['filter_emojis']) {
            $builder->addEventListener(FormEvents::PRE_SUBMIT, [__CLASS__, 'filterEmojis'], 10);
        }

        if ($options['purify_html']) {
            $builder->addEventListener(FormEvents::SUBMIT, [__CLASS__, 'purifyHtml']);
        }

        if ($options['format_title_case']) {
            $builder->addEventListener(FormEvents::SUBMIT, [__CLASS__, 'formatAsTitle']);
        }
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        if (isset($options['with_character_count'])) {
            $view->vars['with_character_count'] = $options['with_character_count'];
        }
    }

    public function getExtendedType()
    {
        return TextType::class;
    }

    public static function filterEmojis(FormEvent $event): void
    {
        $data = $event->getData();

        if (\is_string($data) && '' !== $data) {
            $event->setData(EmojisRemover::remove($data));
        }
    }

    public static function purifyHtml(FormEvent $event): void
    {
        $data = $event->getData();

        if (\is_string($data) && '' !== $data) {
            $event->setData(HtmlPurifier::purify($data));
        }
    }

    public static function formatAsTitle(FormEvent $event): void
    {
        $data = $event->getData();

        if (\is_string($data) && '' !== $data) {
            $event->setData(\mb_convert_case($data, MB_CASE_TITLE, 'UTF-8'));
        }
    }
}
