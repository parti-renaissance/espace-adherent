<?php

namespace App\Form\Admin;

use App\Entity\Team\Member;
use Sonata\AdminBundle\Model\ModelManagerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MemberType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('adherent', MemberAdherentAutocompleteType::class, [
                'model_manager' => $options['model_manager'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class' => Member::class,
            ])
            ->setDefined('model_manager')
            ->setAllowedTypes('model_manager', ModelManagerInterface::class)
        ;
    }
}
