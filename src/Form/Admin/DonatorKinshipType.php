<?php

namespace AppBundle\Form\Admin;

use AppBundle\Entity\Donator;
use AppBundle\Entity\DonatorKinship;
use Sonata\AdminBundle\Form\Type\ModelAutocompleteType;
use Sonata\AdminBundle\Model\ModelManagerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DonatorKinshipType extends AbstractType
{
    private $modelManager;

    public function __construct(ModelManagerInterface $modelManager)
    {
        $this->modelManager = $modelManager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('related', ModelAutocompleteType::class, [
                'label' => 'Donateur',
                'minimum_input_length' => 1,
                'items_per_page' => 20,
                'property' => [
                    'identifier',
                    'firstName',
                    'lastName',
                    'emailAddress',
                ],
                'model_manager' => $this->modelManager,
                'class' => Donator::class,
                'template' => 'admin/donator/donator_type_model_autocomplete.html.twig',
                'admin_code' => 'app.admin.donation',
                'req_params' => [
                    'field' => 'donator',
                ],
            ])
            ->add('kinship', TextType::class, [
                'label' => 'Lien de parenté',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => DonatorKinship::class,
        ]);
    }
}
