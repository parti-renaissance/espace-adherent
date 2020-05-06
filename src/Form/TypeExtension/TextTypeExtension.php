<?php

namespace App\Form\TypeExtension;

use App\Utils\EmojisRemover;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TextTypeExtension extends AbstractTypeExtension
{
    public function getExtendedType()
    {
        return TextType::class;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'empty_data' => function (Options $options) {
                    return $options['required'] ? '' : null;
                },
                'filter_emojis' => false,
                'format_title_case' => false,
                'format_identity_case' => false,
                'with_character_count' => false,
            ])
            ->setAllowedTypes('filter_emojis', 'bool')
            ->setAllowedTypes('format_title_case', 'bool')
            ->setAllowedTypes('format_identity_case', 'bool')
            ->setAllowedTypes('with_character_count', 'bool')
        ;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if ($options['filter_emojis']) {
            $builder->addEventListener(FormEvents::PRE_SUBMIT, [__CLASS__, 'filterEmojis'], 10);
        }

        if ($options['format_title_case']) {
            $builder->addEventListener(FormEvents::SUBMIT, [__CLASS__, 'formatDataAsTitle']);
        }

        if ($options['format_identity_case']) {
            $builder->addEventListener(FormEvents::SUBMIT, [__CLASS__, 'formatDataAsIdentity']);
        }
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['with_character_count'] = $options['with_character_count'];
    }

    public static function filterEmojis(FormEvent $event): void
    {
        $data = $event->getData();

        if (\is_string($data) && '' !== $data) {
            $event->setData(EmojisRemover::remove($data));
        }
    }

    public static function formatDataAsTitle(FormEvent $event): void
    {
        $data = $event->getData();

        if (\is_string($data) && '' !== $data) {
            $event->setData(self::formatTitleCase($data));
        }
    }

    public static function formatDataAsIdentity(FormEvent $event): void
    {
        $data = $event->getData();

        if (\is_string($data) && '' !== $data) {
            $event->setData(self::formatIdentityCase($data));
        }
    }

    public static function formatTitleCase(string $string): string
    {
        // Here we just ensure the first character is uppercase, the rest is left as is
        return \mb_strtoupper(\mb_substr($string, 0, 1)).\mb_substr($string, 1);
    }

    public static function formatIdentityCase(string $string): string
    {
        return \preg_replace_callback_array([
            '/(?:^|[\s-])d[eu][\s-]/ui' => function (array $matches) {
                return \mb_strtolower($matches[0]);
            },
            '/(?:^|[\s-])(d\')(\p{L})/ui' => function (array $matches) {
                return \mb_strtolower($matches[1]).\ucfirst($matches[2]);
            },
        ], \mb_convert_case($string, \MB_CASE_TITLE, 'UTF-8'));
    }
}
