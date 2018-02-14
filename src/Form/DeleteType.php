<?php

namespace AppBundle\Form;

use AppBundle\Validator\Delete;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DeleteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('text_confirmation', TextType::class, [
                'label' => 'Pour confirmer la suppression entré "'.$options['delete_validation'].'"',
                'attr' => [
                    'placeholder' => $options['delete_validation'],
                ],
                'constraints' => [
                    new Delete(['sameText' => $options['delete_validation']]),
                ],
            ])
            ->add('delete', SubmitType::class, [
                'label' => 'Supprimer',
            ])
            ->setMethod(Request::METHOD_DELETE)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('delete_validation', 'Supprimer');
        $resolver->setAllowedTypes('delete_validation', ['string']);
    }
}
