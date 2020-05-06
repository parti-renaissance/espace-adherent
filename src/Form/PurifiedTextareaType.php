<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PurifiedTextareaType extends AbstractType
{
    private $purifierTransformers;

    /**
     * @param DataTransformerInterface[]|array $purifierTransformers
     */
    public function __construct(array $purifierTransformers)
    {
        $this->purifierTransformers = $purifierTransformers;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addViewTransformer($this->purifierTransformers[$options['purifier_type']]);
    }

    public function getParent()
    {
        return TextareaType::class;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'purifier_type' => 'default',
        ]);

        $resolver->setAllowedValues('purifier_type', array_keys($this->purifierTransformers));
    }
}
