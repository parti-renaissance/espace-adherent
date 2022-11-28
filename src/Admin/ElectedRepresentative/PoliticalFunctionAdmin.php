<?php

namespace App\Admin\ElectedRepresentative;

use App\Entity\ElectedRepresentative\Mandate;
use App\Entity\ElectedRepresentative\PoliticalFunctionNameEnum;
use App\Repository\ElectedRepresentative\MandateRepository;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Sonata\Form\Type\DatePickerType;
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

    protected function configureRoutes(RouteCollectionInterface $collection): void
    {
        $collection->clearExcept(['create', 'edit', 'delete']);
    }

    protected function configureFormFields(FormMapper $formMapper): void
    {
        $mandates = [];
        $electedRepresentativeId = $this->getRequest()->attributes->has('id') ? $this->getRequest()->attributes->getInt('id') : $this->getRequest()->query->getInt('objectId');
        if ($electedRepresentativeId) {
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
            ->add('beginAt', DatePickerType::class, [
                'label' => 'Date de début',
            ])
            ->add('finishAt', DatePickerType::class, [
                'label' => 'Date de fin',
                'required' => false,
                'error_bubbling' => false,
            ])
        ;
    }
}
