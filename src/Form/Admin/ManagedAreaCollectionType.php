<?php

namespace AppBundle\Form\Admin;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\District;
use AppBundle\Entity\ReferentTag;
use AppBundle\Form\DataTransformer\ManagedAreaCollectionTransformer;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ManagedAreaCollectionType extends AbstractType
{
    public const DEPUTY_DISTRICT = 'deputy_district';
    public const COMMUNICATION_MANAGER_TAGS = 'communication_manager_tags';
    public const ELECTED_OFFICER_TAGS = 'elected_officer_tags';
    public const REFERENT_TAGS = 'referent_tags';
    public const SENATOR_TAGS = 'senator_tags';

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(self::REFERENT_TAGS, EntityType::class, [
                'class' => ReferentTag::class,
                'label' => 'managed_area.referent.label',
                'multiple' => true,
            ])
            ->add(self::DEPUTY_DISTRICT, EntityType::class, [
                'class' => District::class,
                'label' => 'managed_area.deputy.label',
                'required' => false,
            ])
            ->add(self::SENATOR_TAGS, EntityType::class, [
                'class' => ReferentTag::class,
                'label' => 'managed_area.senator.label',
                'multiple' => true,
            ])
            ->add(self::ELECTED_OFFICER_TAGS, EntityType::class, [
                'class' => ReferentTag::class,
                'label' => 'managed_area.elected_officer.label',
                'multiple' => true,
            ])
            ->add(self::COMMUNICATION_MANAGER_TAGS, EntityType::class, [
                'class' => ReferentTag::class,
                'label' => 'managed_area.communication_manager.label',
                'multiple' => true,
            ])
        ;

        $builder->addModelTransformer(new ManagedAreaCollectionTransformer($options['adherent']));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired('adherent');
        $resolver->setAllowedTypes('adherent', Adherent::class);
    }
}
