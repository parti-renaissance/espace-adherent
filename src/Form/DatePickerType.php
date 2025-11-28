<?php

declare(strict_types=1);

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DatePickerType extends AbstractType
{
    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        if (isset($view->vars['attr']['class'])) {
            $view->vars['attr']['class'] .= ' em-datetime-picker';
        } else {
            $view->vars['attr']['class'] = 'em-datetime-picker';
        }
        $view->vars['attr']['data-datetimepicker'] = json_encode([
            'enableTime' => false,
            'dateFormat' => $options['date_format'],
            'altInput' => isset($options['human_friendly_format']),
            'altFormat' => $options['human_friendly_format'] ?? '',
            'minDate' => isset($options['min_date']) ? $options['min_date']->format($options['date_format']) : null,
            'maxDate' => isset($options['max_date']) ? $options['max_date']->format($options['date_format']) : null,
            'minuteIncrement' => $options['minute_increment'],
            'defaultMinute' => $options['default_minute'],
            'disable' => $options['disable_dates'],
            'enable' => $options['enable_dates'],
            'locale' => $options['locale'],
            'inline' => $options['always_display'],
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'widget' => 'single_text',
            'attr' => [
                'placeholder' => 'jj / mm / aaaa',
            ],
            'date_format' => 'Y-m-d',
            'human_friendly_format' => 'd/m/Y',
            'min_date' => null,
            'max_date' => null,
            'default_minute' => 0,
            'minute_increment' => 5,
            'enable_dates' => [],
            'disable_dates' => [],
            // example for 'enable_dates', 'disable_dates', and their format should correspond to 'date_format'
            // 'disable_dates' => [[
            //     'from' => new \DateTime('now'),
            //     'to' => new \DateTime('+7 days'),
            // ], new \DateTime('2021-01-01'),
            'locale' => null,
            'always_display' => false,
        ]);
        $resolver->setAllowedTypes('date_format', 'string');
        $resolver->setAllowedTypes('human_friendly_format', ['null', 'string']);
        $resolver->setAllowedTypes('min_date', ['null', \DateTime::class]);
        $resolver->setAllowedTypes('max_date', ['null', \DateTime::class]);
        $resolver->setAllowedTypes('default_minute', ['int']);
        $resolver->setAllowedTypes('minute_increment', ['int']);
        $resolver->setAllowedTypes('enable_dates', ['null', 'array']);
        $resolver->setAllowedTypes('disable_dates', ['null', 'array']);
        $resolver->setAllowedTypes('locale', ['null', 'array']);
        $resolver->setAllowedTypes('always_display', 'bool');

        parent::configureOptions($resolver);
    }

    public function getParent(): string
    {
        return DateType::class;
    }
}
