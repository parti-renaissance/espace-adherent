<?php

declare(strict_types=1);

namespace App\Admin\Procuration;

use App\Admin\AbstractAdmin;
use App\Admin\Exporter\IterableCallbackDataSourceTrait;
use App\Admin\Exporter\IteratorCallbackDataSource;
use App\Entity\Adherent;
use App\Entity\Procuration\ProcurationRequest;
use App\Form\Admin\Procuration\InitialRequestTypeEnumType;
use App\Utils\PhoneNumberUtils;
use App\Utils\PhpConfigurator;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\DoctrineORMAdminBundle\Filter\ChoiceFilter;
use Sonata\DoctrineORMAdminBundle\Filter\DateRangeFilter;
use Sonata\Form\Type\DateRangePickerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class ProcurationRequestAdmin extends AbstractAdmin
{
    use IterableCallbackDataSourceTrait;

    protected function configureFormFields(FormMapper $form): void
    {
        $form->add('email', TextType::class, ['label' => 'Adresse email']);
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->addIdentifier('email', null, [
                'label' => 'Adresse email',
            ])
            ->add('type', null, [
                'label' => 'Type',
                'template' => 'admin/procuration/_list_initial_request_type.html.twig',
            ])
            ->add('adherent', null, [
                'label' => 'Adhérent',
            ])
            ->add('createdAt', null, [
                'label' => 'Date',
            ])
            ->add(ListMapper::NAME_ACTIONS, null, [
                'actions' => [
                    'edit' => [],
                    'delete' => [],
                ],
            ])
        ;
    }

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add('email', null, [
                'label' => 'Adresse email',
                'show_filter' => true,
            ])
            ->add('type', ChoiceFilter::class, [
                'label' => 'Type',
                'show_filter' => true,
                'field_type' => InitialRequestTypeEnumType::class,
                'field_options' => [
                    'multiple' => true,
                ],
            ])
            ->add('createdAt', DateRangeFilter::class, [
                'label' => 'Date de création',
                'field_type' => DateRangePickerType::class,
                'show_filter' => true,
            ])
        ;
    }

    protected function configureExportFields(): array
    {
        PhpConfigurator::disableMemoryLimit();

        $translator = $this->getTranslator();

        return [IteratorCallbackDataSource::CALLBACK => static function (array $procuration) use ($translator) {
            /** @var ProcurationRequest $procurationRequest */
            $procurationRequest = $procuration[0];
            $adherent = $procurationRequest->adherent;

            return [
                'ID' => $procurationRequest->getId(),
                'UUID' => $procurationRequest->getUuid()->toString(),
                'Adresse email' => $procurationRequest->email,
                'Type' => $translator->trans('procuration.initial_request.type.'.$procurationRequest->type->value),
                'Adhérent' => $adherent instanceof Adherent ? 'oui' : 'non',
                'Téléphone adhérent' => PhoneNumberUtils::format($adherent?->getPhone()),
                'Créé le' => $procurationRequest->getCreatedAt()->format('Y/m/d H:i:s'),
            ];
        }];
    }
}
