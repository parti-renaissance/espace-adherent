<?php

namespace App\Form\Admin;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UnlayerContentType extends AbstractType
{
    public function __construct(private readonly string $unlayerDefaultTemplateId)
    {
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['unlayer_template_id'] = $options['unlayer_template_id'];
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefined('unlayer_template_id');
        $resolver->setAllowedTypes('unlayer_template_id', ['int']);
        $resolver->setDefault('unlayer_template_id', (int) $this->unlayerDefaultTemplateId);

        $resolver->setDefaults(['error_bubbling' => false]);
    }

    public function getParent()
    {
        return HiddenType::class;
    }
}
