<?php

namespace App\Admin;

use App\Entity\GeneralMeeting\GeneralMeetingReport;
use App\GeneralMeeting\GeneralMeetingReportHandler;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\ModelAutocompleteType;
use Sonata\DoctrineORMAdminBundle\Filter\DateRangeFilter;
use Sonata\Form\Type\DatePickerType;
use Sonata\Form\Type\DateRangePickerType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class GeneralMeetingReportAdmin extends AbstractAdmin
{
    private GeneralMeetingReportHandler $generalMeetingReportHandler;

    protected function configureDatagridFilters(DatagridMapper $datagridMapper): void
    {
        $datagridMapper
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

    protected function configureListFields(ListMapper $listMapper): void
    {
        $listMapper
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

    protected function configureFormFields(FormMapper $formMapper): void
    {
        $formMapper
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
                ])
                ->add('zone', ModelAutocompleteType::class, [
                    'property' => ['name', 'code'],
                    'required' => false,
                    'help' => 'Laissez vide pour appliquer une visibilité nationale.',
                    'btn_add' => false,
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

    /**
     * @required
     */
    public function setGeneralMeetingReportHandler(GeneralMeetingReportHandler $generalMeetingReportHandler): void
    {
        $this->generalMeetingReportHandler = $generalMeetingReportHandler;
    }
}
