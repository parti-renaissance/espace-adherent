<?php

declare(strict_types=1);

namespace App\Admin;

use App\Admin\Exporter\IterableCallbackDataSourceTrait;
use App\Admin\Exporter\IteratorCallbackDataSource;
use App\Entity\CertificationRequest;
use App\Entity\Geo\Region;
use App\Utils\PhoneNumberUtils;
use App\Utils\PhpConfigurator;
use Doctrine\ORM\Query\Expr;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridInterface;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Filter\Model\FilterData;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Sonata\DoctrineORMAdminBundle\Datagrid\ProxyQuery;
use Sonata\DoctrineORMAdminBundle\Filter\CallbackFilter;
use Sonata\DoctrineORMAdminBundle\Filter\ChoiceFilter;
use Sonata\DoctrineORMAdminBundle\Filter\DateRangeFilter;
use Sonata\Form\Type\DateRangePickerType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class CertificationRequestAdmin extends AbstractAdmin
{
    use IterableCallbackDataSourceTrait;

    protected function configureDefaultSortValues(array &$sortValues): void
    {
        parent::configureDefaultSortValues($sortValues);

        $sortValues[DatagridInterface::SORT_BY] = 'createdAt';
        $sortValues[DatagridInterface::SORT_ORDER] = 'DESC';
        $sortValues[DatagridInterface::PER_PAGE] = 128;
    }

    protected function getAccessMapping(): array
    {
        return [
            'approve' => 'APPROVE',
            'refuse' => 'REFUSE',
            'block' => 'BLOCK',
            'document' => 'DOCUMENT',
        ];
    }

    protected function configureRoutes(RouteCollectionInterface $collection): void
    {
        $collection
            ->clearExcept(['list', 'show', 'export'])
            ->add('approve', $this->getRouterIdParameter().'/approve')
            ->add('refuse', $this->getRouterIdParameter().'/refuse')
            ->add('block', $this->getRouterIdParameter().'/block')
            ->add('document', $this->getRouterIdParameter().'/document')
        ;
    }

    /**
     * @param CertificationRequest|null $object
     */
    protected function configureActionButtons(array $buttonList, string $action, ?object $object = null): array
    {
        if (\in_array($action, ['approve', 'refuse', 'block'], true)) {
            $actions = parent::configureActionButtons($buttonList, 'show', $object);
        } else {
            $actions = parent::configureActionButtons($buttonList, $action, $object);
        }

        if ('show' === $action) {
            if ($this->hasAccess('approve', $object) && $this->hasRoute('approve')) {
                $actions['approve'] = ['template' => 'admin/certification_request/action_button_approve.html.twig'];
            }

            if ($this->hasAccess('refuse', $object) && $this->hasRoute('refuse')) {
                $actions['refuse'] = ['template' => 'admin/certification_request/action_button_refuse.html.twig'];
            }

            if ($this->hasAccess('block', $object) && $this->hasRoute('block')) {
                $actions['block'] = ['template' => 'admin/certification_request/action_button_block.html.twig'];
            }
        }

        return $actions;
    }

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add('adherent.firstName', null, [
                'label' => 'Prénom',
                'show_filter' => true,
            ])
            ->add('adherent.lastName', null, [
                'label' => 'Nom',
                'show_filter' => true,
            ])
            ->add('adherent.emailAddress', null, [
                'label' => 'Email',
                'show_filter' => true,
            ])
            ->add('region', CallbackFilter::class, [
                'label' => 'Région',
                'mapped' => false,
                'show_filter' => true,
                'field_type' => EntityType::class,
                'field_options' => [
                    'class' => Region::class,
                ],
                'callback' => function (ProxyQuery $qb, string $alias, string $field, FilterData $value) {
                    if (!$value->hasValue()) {
                        return false;
                    }

                    /** @var Region $region */
                    $region = $value->getValue();
                    $qb->innerJoin("$alias.adherent", 'adherent');

                    $postalCodeCondition = new Expr\Orx();
                    foreach ($region->getDepartments() as $key => $department) {
                        $postalCodeCondition->add("adherent.postAddress.postalCode LIKE :postal_code_$key");
                        $qb->setParameter("postal_code_$key", $department->getCode().'%');
                    }

                    $qb->andWhere($postalCodeCondition);

                    return true;
                },
            ])
            ->add('status', ChoiceFilter::class, [
                'label' => 'Statut',
                'show_filter' => true,
                'field_type' => ChoiceType::class,
                'field_options' => [
                    'choices' => CertificationRequest::STATUS_CHOICES,
                    'choice_label' => function (string $choice) {
                        return "certification_request.status.$choice";
                    },
                    'multiple' => true,
                ],
            ])
            ->add('ocrStatus', ChoiceFilter::class, [
                'label' => 'Statut OCR',
                'show_filter' => true,
                'field_type' => ChoiceType::class,
                'field_options' => [
                    'choices' => CertificationRequest::OCR_STATUS_CHOICES,
                    'choice_label' => function (string $choice) {
                        return "certification_request.status.$choice";
                    },
                    'multiple' => true,
                ],
            ])
            ->add('createdAt', DateRangeFilter::class, [
                'label' => 'Date de création',
                'show_filter' => true,
                'field_type' => DateRangePickerType::class,
            ])
            ->add('processedBy', null, [
                'label' => 'Traitée par',
            ])
            ->add('automaticallyProcessed', CallbackFilter::class, [
                'label' => 'Traité automatiquement',
                'mapped' => false,
                'show_filter' => true,
                'field_type' => ChoiceType::class,
                'field_options' => [
                    'choices' => [
                        'Tous' => null,
                        'global.yes' => true,
                        'global.no' => false,
                    ],
                ],
                'callback' => function (ProxyQuery $qb, string $alias, string $field, FilterData $value) {
                    if (!$value->hasValue()) {
                        return false;
                    }

                    $qb
                        ->andWhere("$alias.status != :pending_status")
                        ->setParameter('pending_status', CertificationRequest::STATUS_PENDING)
                    ;

                    switch ($value->getValue()) {
                        case true:
                            $qb->andWhere("$alias.processedBy IS NULL");

                            break;
                        case false:
                            $qb->andWhere("$alias.processedBy IS NOT NULL");

                            break;
                    }

                    return true;
                },
            ])
            ->add('processedAt', DateRangeFilter::class, [
                'label' => 'Date de traitement',
                'field_type' => DateRangePickerType::class,
            ])
        ;
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->addIdentifier('id', null, [
                'label' => 'ID',
            ])
            ->add('adherent', null, [
                'label' => 'Adhérent',
                'template' => 'admin/certification_request/list_adherent.html.twig',
            ])
            ->add('status', null, [
                'label' => 'Statut',
                'template' => 'admin/certification_request/list_status.html.twig',
            ])
            ->add('createdAt', null, [
                'label' => 'Date de création',
            ])
            ->add('processedBy', null, [
                'label' => 'Traitée par',
                'template' => 'admin/certification_request/list_processed_by.html.twig',
            ])
            ->add('processedAt', null, [
                'label' => 'Date de traitement',
            ])
            ->add(ListMapper::NAME_ACTIONS, null, [
                'virtual_field' => true,
                'actions' => [
                    'show' => [],
                ],
            ])
        ;
    }

    protected function configureExportFields(): array
    {
        PhpConfigurator::disableMemoryLimit();

        return [IteratorCallbackDataSource::CALLBACK => static function (array $certificationRequest) {
            /** @var CertificationRequest $certificationRequest */
            $certificationRequest = $certificationRequest[0];
            $adherent = $certificationRequest->getAdherent();

            $phone = PhoneNumberUtils::format($adherent->getPhone());
            $birthDate = $adherent->getBirthdate();

            return [
                'id' => $certificationRequest->getId(),
                'Date' => $certificationRequest->getCreatedAt()->format('Y/m/d H:i:s'),
                'Status' => $certificationRequest->getStatus(),
                'Nom' => $adherent->getLastName(),
                'Prénom' => $adherent->getFirstName(),
                'Date de naissance' => $birthDate?->format('Y/m/d H:i:s'),
                'Nationalité' => $adherent->getNationality(),
                'Addresse' => $adherent->getAddress(),
                'Code postal' => $adherent->getPostalCode(),
                'Ville' => $adherent->getCityName(),
                'Pays' => $adherent->getCountry(),
                'Téléphone adhérent' => $phone,
                'Email' => $adherent->getEmailAddress(),
                'uuid' => $certificationRequest->getUuid(),
            ];
        },
        ];
    }
}
