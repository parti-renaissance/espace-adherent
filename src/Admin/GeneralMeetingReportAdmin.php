<?php

declare(strict_types=1);

namespace App\Admin;

use App\Entity\GeneralMeeting\GeneralMeetingReport;
use App\Entity\Geo\Zone;
use App\Form\Admin\AdminZoneAutocompleteType;
use App\GeneralMeeting\GeneralMeetingReportHandler;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\DoctrineORMAdminBundle\Filter\DateRangeFilter;
use Sonata\Form\Type\DatePickerType;
use Sonata\Form\Type\DateRangePickerType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class GeneralMeetingReportAdmin extends AbstractAdmin
{
    public function __construct(private readonly GeneralMeetingReportHandler $generalMeetingReportHandler)
    {
        parent::__construct();
    }

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add('title', null, [
                'label' => 'Titre',
                'show_filter' => true,
            ])
            ->add('date', DateRangeFilter::class, [
                'label' => 'Date',
                'field_type' => DateRangePickerType::class,
            ])
        ;
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->addIdentifier('title', null, [
                'label' => 'Titre',
            ])
            ->add('date', null, [
                'label' => 'Daté du',
            ])
            ->add(ListMapper::NAME_ACTIONS, null, [
                'virtual_field' => true,
                'actions' => [
                    'edit' => [],
                    'delete' => [],
                ],
            ])
        ;
    }

    protected function configureFormFields(FormMapper $form): void
    {
        $form
            ->with('Information')
                ->add('title', TextType::class, [
                    'label' => 'Titre',
                ])
                ->add('description', TextareaType::class, [
                    'label' => 'Description',
                    'required' => false,
                ])
                ->add('date', DatePickerType::class, [
                    'label' => 'Date',
                    'datepicker_options' => [
                        'useCurrent' => false,
                        'restrictions' => [
                            'maxDate' => new \DateTime(),
                        ],
                    ],
                ])
                ->add('zone', AdminZoneAutocompleteType::class, [
                    'label' => 'Département',
                    'btn_add' => false,
                    'zone_types' => [Zone::DEPARTMENT],
                ])
            ->end()
            ->with('Contenu', ['box_class' => 'box box-success'])
                ->add('file', FileType::class, [
                    'label' => false,
                    'required' => false,
                ])
            ->end()
        ;
    }

    /**
     * @param GeneralMeetingReport $object
     */
    protected function prePersist(object $object): void
    {
        $this->generalMeetingReportHandler->handleFile($object);
    }

    /**
     * @param GeneralMeetingReport $object
     */
    protected function preUpdate(object $object): void
    {
        $this->generalMeetingReportHandler->handleFile($object);
    }
}
