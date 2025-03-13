<?php

namespace App\Admin;

use App\Entity\GeneralMeeting\GeneralMeetingReport;
use App\Entity\Geo\Zone;
use App\GeneralMeeting\GeneralMeetingReportHandler;
use Doctrine\ORM\QueryBuilder;
use Sonata\AdminBundle\Admin\AdminInterface;
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
use Symfony\Contracts\Service\Attribute\Required;

class GeneralMeetingReportAdmin extends AbstractAdmin
{
    private GeneralMeetingReportHandler $generalMeetingReportHandler;

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
                ->add('zone', ModelAutocompleteType::class, [
                    'property' => ['name', 'code'],
                    'label' => 'Département',
                    'btn_add' => false,
                    'callback' => [$this, 'prepareZoneAutocompleteCallback'],
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

    public static function prepareZoneAutocompleteCallback(
        AdminInterface $admin,
        array $properties,
        string $value,
    ): void {
        /** @var QueryBuilder $qb */
        $qb = $admin->getDatagrid()->getQuery();
        $alias = $qb->getRootAliases()[0];

        $orx = $qb->expr()->orX();
        foreach ($properties as $property) {
            $orx->add($alias.'.'.$property.' LIKE :property_'.$property);
            $qb->setParameter('property_'.$property, '%'.$value.'%');
        }
        $qb
            ->orWhere($orx)
            ->andWhere(\sprintf('%1$s.type = :type AND %1$s.active = 1', $alias))
            ->setParameter('type', Zone::DEPARTMENT)
        ;
    }

    #[Required]
    public function setGeneralMeetingReportHandler(GeneralMeetingReportHandler $generalMeetingReportHandler): void
    {
        $this->generalMeetingReportHandler = $generalMeetingReportHandler;
    }
}
