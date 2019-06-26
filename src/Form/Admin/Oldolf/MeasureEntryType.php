<?php

namespace AppBundle\Form\Admin\Oldolf;

use AppBundle\Oldolf\MeasureChoiceLoader;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class MeasureEntryType extends AbstractType
{
    private $measureChoiceLoader;

    public function __construct(MeasureChoiceLoader $measureChoiceLoader)
    {
        $this->measureChoiceLoader = $measureChoiceLoader;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('key', ChoiceType::class, [
                'label' => false,
                'choices' => $this->measureChoiceLoader->getKeyChoices(),
                'placeholder' => 'Sélectionnez une clé',
                'choice_translation_domain' => false,
                'attr' => [
                    'class' => 'measure-entry-key-select',
                    'data-sonata-select2' => 'false',
                ],
            ])
            ->add('value', TextType::class, [
                'label' => false,
                'attr' => [
                    'placeholder' => 'Entrez une valeur',
                ],
            ])
        ;
    }
}
