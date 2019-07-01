<?php

namespace AppBundle\Form\Admin\Oldolf;

use AppBundle\Entity\Oldolf\Measure;
use AppBundle\Oldolf\MeasureChoiceLoader;
use Sonata\AdminBundle\Form\Type\CollectionType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
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
            ->add('type', ChoiceType::class, [
                'label' => 'Type',
                'choices' => $this->measureChoiceLoader->getTypeChoices(),
                'placeholder' => 'Selectionnez un type',
                'choice_translation_domain' => 'forms',
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
