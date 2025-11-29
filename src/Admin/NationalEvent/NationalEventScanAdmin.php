<?php

declare(strict_types=1);

namespace App\Admin\NationalEvent;

use App\Admin\AbstractAdmin;
use App\Entity\NationalEvent\EventInscription;
use App\NationalEvent\InscriptionStatusEnum;
use App\Repository\NationalEvent\NationalEventRepository;
use Doctrine\ORM\QueryBuilder;
use Sonata\AdminBundle\Datagrid\DatagridInterface;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\Type\ModelAutocompleteType;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Sonata\DoctrineORMAdminBundle\Filter\ChoiceFilter;
use Sonata\DoctrineORMAdminBundle\Filter\DateTimeRangeFilter;
use Sonata\DoctrineORMAdminBundle\Filter\ModelFilter;
use Sonata\Form\Type\DateTimeRangePickerType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class NationalEventScanAdmin extends AbstractAdmin
{
    protected function configureDefaultSortValues(array &$sortValues): void
    {
        $sortValues[DatagridInterface::SORT_BY] = 'createdAt';
        $sortValues[DatagridInterface::SORT_ORDER] = 'DESC';
    }

    protected function configureRoutes(RouteCollectionInterface $collection): void
    {
        $collection->clearExcept(['list', 'export']);
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->add('inscription.event', null, ['label' => 'Event'])
            ->add('inscription', null, ['label' => 'Inscrit', 'template' => 'admin/national_event/list_identity.html.twig'])
            ->add('inscriptionStatus', 'trans', ['label' => 'Statut', 'header_style' => 'min-width: 160px;'])
            ->add('scannedBy', null, ['label' => 'Scanné par'])
            ->add('createdAt', null, ['label' => 'Date de scan'])
        ;
    }

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add('inscription', ModelFilter::class, [
                'label' => 'Inscrit',
                'show_filter' => true,
                'field_type' => ModelAutocompleteType::class,
                'field_options' => [
                    'property' => ['search'],
                    'minimum_input_length' => 1,
                    'to_string_callback' => function (EventInscription $inscription): string {
                        return \sprintf(
                            '%s %s (%s, %s)',
                            $inscription->firstName,
                            $inscription->lastName,
                            $inscription->event->getName(),
                            $this->getTranslator()->trans($inscription->status)
                        );
                    },
                ],
            ])
            ->add('inscription.event', null, [
                'label' => 'Event',
                'show_filter' => true,
                'field_options' => [
                    'query_builder' => function (NationalEventRepository $er): QueryBuilder {
                        return $er
                            ->createQueryBuilder('e')
                            ->orderBy('e.startDate', 'DESC')
                        ;
                    },
                ],
            ])
            ->add('inscriptionStatus', ChoiceFilter::class, [
                'label' => 'Statut de l\'inscription au moment du scan',
                'show_filter' => true,
                'field_type' => ChoiceType::class,
                'field_options' => [
                    'multiple' => true,
                    'choices' => array_combine(InscriptionStatusEnum::STATUSES, InscriptionStatusEnum::STATUSES),
                ],
            ])
            ->add('createdAt', DateTimeRangeFilter::class, [
                'label' => 'Date de scan',
                'show_filter' => true,
                'field_type' => DateTimeRangePickerType::class,
            ])
        ;
    }
}
