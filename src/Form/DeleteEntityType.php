<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DeleteEntityType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('delete', SubmitType::class, [
                'label' => 'Supprimer',
            ])
            ->setMethod(Request::METHOD_DELETE)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setAllowedTypes('csrf_token_id', 'string');
    }
}
