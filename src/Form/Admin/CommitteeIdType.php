<?php

namespace App\Form\Admin;

use App\Form\DataTransformer\CommitteeToIdTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CommitteeIdType extends AbstractType
{
    private $transformer;

    public function __construct(CommitteeToIdTransformer $transformer)
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
            'invalid_message' => 'committee.unknown_id',
        ]);
    }

    public function getParent()
    {
        return IntegerType::class;
    }
}
