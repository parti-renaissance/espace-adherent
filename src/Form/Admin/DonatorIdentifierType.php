<?php

namespace AppBundle\Form\Admin;

use AppBundle\Form\DataTransformer\DonatorToIdentifierTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DonatorIdentifierType extends AbstractType
{
    private $transformer;

    public function __construct(DonatorToIdentifierTransformer $transformer)
    {
        $this->transformer = $transformer;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addModelTransformer($this->transformer);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'invalid_message' => 'donator.unknown_id',
        ]);
    }

    public function getParent()
    {
        return TextType::class;
    }
}
