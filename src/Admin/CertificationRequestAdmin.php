<?php

namespace AppBundle\Admin;

use AppBundle\Entity\CertificationRequest;
use AppBundle\Utils\PhoneNumberFormatter;
use AppBundle\Utils\PhpConfigurator;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\DoctrineORMAdminBundle\Filter\ChoiceFilter;
use Sonata\DoctrineORMAdminBundle\Filter\DateRangeFilter;
use Sonata\Exporter\Source\IteratorCallbackSourceIterator;
use Sonata\Form\Type\DateRangePickerType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class CertificationRequestAdmin extends AbstractAdmin
{
    protected $datagridValues = [
        '_page' => 1,
        '_per_page' => 128,
        '_sort_order' => 'DESC',
        '_sort_by' => 'createdAt',
    ];

    protected $accessMapping = [
        'approve' => 'APPROVE',
        'refuse' => 'REFUSE',
        'document' => 'DOCUMENT',
    ];

    public function configureRoutes(RouteCollection $collection)
    {
        $collection
            ->clearExcept(['list', 'show', 'export'])
            ->add('approve', $this->getRouterIdParameter().'/approve')
            ->add('refuse', $this->getRouterIdParameter().'/refuse')
            ->add('document', $this->getRouterIdParameter().'/document')
        ;
    }

    public function configureActionButtons($action, $object = null)
    {
        if (\in_array($action, ['approve', 'refuse'], true)) {
            $actions = parent::configureActionButtons('show', $object);
        } else {
            $actions = parent::configureActionButtons($action, $object);
        }

        if ('show' === $action && $object->isPending()) {
            $actions = array_merge($actions, [
                'approve' => ['template' => 'admin/certification_request/action_button_approve.html.twig'],
                'refuse' => ['template' => 'admin/certification_request/action_button_refuse.html.twig'],
            ]);
        }

        return $actions;
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
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
            ->add('status', ChoiceFilter::class, [
                'label' => 'Statut',
                'show_filter' => true,
                'field_type' => ChoiceType::class,
                'field_options' => [
                    'choices' => [
                        CertificationRequest::STATUS_PENDING,
                        CertificationRequest::STATUS_APPROVED,
                        CertificationRequest::STATUS_REFUSED,
                    ],
                    'choice_label' => function (string $choice) {
                        return "certification_request.status.$choice";
                    },
                    'multiple' => true,
                ],
            ])
            ->add('createdAt', DateRangeFilter::class, [
                'label' => 'Date',
                'show_filter' => true,
                'field_type' => DateRangePickerType::class,
            ])
        ;
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('adherent', null, [
                'label' => 'Adhérent',
                'template' => 'admin/certification_request/list_adherent.html.twig',
            ])
            ->add('status', null, [
                'label' => 'Statut',
                'template' => 'admin/certification_request/list_status.html.twig',
            ])
            ->add('createdAt', null, [
                'label' => 'Date',
            ])
            ->add('_action', null, [
                'virtual_field' => true,
                'actions' => [
                    'show' => [],
                ],
            ])
        ;
    }

    public function getDataSourceIterator()
    {
        PhpConfigurator::disableMemoryLimit();

        return new IteratorCallbackSourceIterator(
            $this->getCertificationRequestIterator(),
            function (array $certificationRequest) {
                /** @var CertificationRequest $certificationRequest */
                $certificationRequest = $certificationRequest[0];
                $adherent = $certificationRequest->getAdherent();

                $phone = PhoneNumberFormatter::format($adherent->getPhone());
                $birthDate = $adherent->getBirthdate();

                return [
                    'id' => $certificationRequest->getId(),
                    'Date' => $certificationRequest->getCreatedAt()->format('Y/m/d H:i:s'),
                    'Status' => $certificationRequest->getStatus(),
                    'Nom' => $adherent->getLastName(),
                    'Prénom' => $adherent->getFirstName(),
                    'Date de naissance' => $birthDate ? $birthDate->format('Y/m/d H:i:s') : null,
                    'Nationalité' => $adherent->getNationality(),
                    'Addresse' => $adherent->getAddress(),
                    'Code postal' => $adherent->getPostalCode(),
                    'Ville' => $adherent->getCityName(),
                    'Pays' => $adherent->getCountry(),
                    'Téléphone adhérent' => $phone,
                    'Email' => $adherent->getEmailAddress(),
                    'uuid' => $certificationRequest->getUuid(),
                ];
            }
        );
    }

    private function getCertificationRequestIterator(): \Iterator
    {
        $datagrid = $this->getDatagrid();
        $datagrid->buildPager();

        $query = $datagrid->getQuery();
        $alias = current($query->getRootAliases());

        $query
            ->select("DISTINCT $alias")
            ->innerJoin("$alias.adherent", 'adherent')
            ->addSelect('adherent')
            ->leftJoin("$alias.processedBy", 'processed_by')
            ->addSelect('processed_by')
        ;
        $query->setFirstResult(0);
        $query->setMaxResults(null);

        return $query->getQuery()->iterate();
    }
}
