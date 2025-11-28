<?php

declare(strict_types=1);

namespace App\Form\TypeExtension;

use App\Form\DataTransformer\NullToStringTransformer;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FormTypeExtension extends AbstractTypeExtension
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        if ($options['cast_null_to_string']) {
            $builder->addModelTransformer(new NullToStringTransformer());
        }
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars['form_full'] = $options['form_full'];
        $view->vars['form_type_class'] = \get_class($form->getConfig()->getType()->getInnerType());
        $view->vars['error_raw'] = $options['error_raw'];
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'cast_null_to_string' => false,
                'form_full' => false,
                'error_raw' => false,
            ])
            ->setAllowedTypes('cast_null_to_string', 'bool')
            ->setAllowedTypes('form_full', 'bool')
            ->setAllowedTypes('error_raw', 'bool')
        ;
    }

    public static function getExtendedTypes(): array
    {
        return [FormType::class];
    }
}
