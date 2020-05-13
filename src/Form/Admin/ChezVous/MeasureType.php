<?php

namespace App\Form\Admin\ChezVous;

use App\ChezVous\MeasureChoiceLoader;
use App\Entity\ChezVous\Measure;
use App\Entity\ChezVous\MeasureType as MeasureTypeEntity;
use Sonata\AdminBundle\Form\Type\CollectionType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MeasureType extends AbstractType
{
    private $measureChoiceLoader;

    public function __construct(MeasureChoiceLoader $measureChoiceLoader)
    {
        $this->measureChoiceLoader = $measureChoiceLoader;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('type', EntityType::class, [
                'label' => 'Type',
                'class' => MeasureTypeEntity::class,
                'placeholder' => 'Selectionnez un type',
                'attr' => [
                    'class' => 'measure-type-select',
                ],
            ])
            ->add('entries', CollectionType::class, [
                'label' => 'Informations',
                'entry_type' => MeasureEntryType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'error_bubbling' => false,
                'attr' => [
                    'class' => 'measure-entries-collection',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('data_class', Measure::class);
    }
}
