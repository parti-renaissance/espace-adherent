<?php

declare(strict_types=1);

namespace App\Form\Admin\Team;

use App\Entity\Team\Member;
use Sonata\AdminBundle\Model\ModelManagerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MemberType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('adherent', MemberAdherentAutocompleteType::class, [
                'model_manager' => $options['model_manager'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
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
