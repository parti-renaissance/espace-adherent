<?php

namespace App\Form;

use App\Entity\CertificationRequest;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CertificationRequestType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('document', FileType::class, [
                'attr' => [
                    'accept' => implode(',', CertificationRequest::MIME_TYPES),
                ],
            ])
            ->add('cgu', RequiredCheckboxType::class)
            ->add('id_processing', RequiredCheckboxType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('data_class', CertificationRequest::class);
    }
}
