<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ColorChoiceType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'choice_label' => function ($value) {
                return $value;
            },
            'expanded' => true,
            'choice_attr' => function () {
                return [
                    'style' => 'display:none;',
                ];
            },
        ]);
    }

    public function getParent(): string
    {
        return ChoiceType::class;
    }

    /**
     * Hack for https://github.com/symfony/symfony/issues/26062
     *
     * Override the "entry" block_prefix by "color_choice_entry"
     * to allow us use "color_choice_entry_*" (with label, errors or widget) as template block name
     * for overriding the default choice_entry_* templates.
     */
    public function finishView(FormView $view, FormInterface $form, array $options): void
    {
        foreach ($view->children as $child) {
            foreach ($child->vars['block_prefixes'] as $idx => $prefix) {
                if (false !== strpos($prefix, 'entry')) {
                    $child->vars['block_prefixes'][$idx] = 'color_choice_entry';
                    $child->vars['block_prefixes'][] = $prefix;
                }
            }
        }
    }
}
