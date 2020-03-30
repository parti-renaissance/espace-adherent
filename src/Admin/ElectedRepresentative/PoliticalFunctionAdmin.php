<?php

namespace AppBundle\Admin\ElectedRepresentative;

use AppBundle\Entity\ElectedRepresentative\Mandate;
use AppBundle\Entity\ElectedRepresentative\PoliticalFunctionNameEnum;
use AppBundle\Repository\ElectedRepresentative\MandateRepository;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class PoliticalFunctionAdmin extends AbstractAdmin
{
    private $mandateRepository;

    public function __construct(
        string $code,
        string $class,
        string $baseControllerName,
        MandateRepository $mandateRepository
    ) {
        parent::__construct($code, $class, $baseControllerName);

        $this->mandateRepository = $mandateRepository;
    }

    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->clearExcept(['create', 'edit', 'delete']);
    }

    protected function configureFormFields(FormMapper $formMapper)
    {
        $mandates = [];
        $electedRepresentativeId = $this->getRequest()->attributes->has('id') ? $this->getRequest()->attributes->getInt('id', null) : $this->getRequest()->query->getInt('objectId', null);
        if (null !== $electedRepresentativeId) {
            $mandates = $this->mandateRepository->getMandatesForPoliticalFunction($electedRepresentativeId);
        }

        $formMapper
            ->add('mandate', EntityType::class, [
                'label' => 'Mandat',
                'placeholder' => '--',
                'class' => Mandate::class,
                'choice_label' => 'number',
                'choices' => $mandates,
            ])
            ->add('name', ChoiceType::class, [
                'label' => 'Nom',
                'placeholder' => '--',
                'choices' => PoliticalFunctionNameEnum::CHOICES,
            ])
            ->add('clarification', TextType::class, [
                'required' => false,
                'label' => 'Précision',
            ])
            ->add('mandateZoneName', TextType::class, [
                'required' => false,
                'label' => 'Périmètre géographique',
                'disabled' => true,
            ])
            ->add('onGoing', CheckboxType::class, [
                'label' => 'En cours',
                'required' => false,
            ])
            ->add('beginAt', 'sonata_type_date_picker', [
                'label' => 'Date de début',
            ])
            ->add('finishAt', 'sonata_type_date_picker', [
                'label' => 'Date de fin',
                'required' => false,
                'error_bubbling' => false,
            ])
        ;
    }
}
