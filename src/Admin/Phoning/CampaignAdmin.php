<?php

namespace App\Admin\Phoning;

use App\Admin\Audience\AudienceAdmin;
use App\Entity\Administrator;
use App\Entity\Team\Team;
use App\Form\Admin\AdminZoneAutocompleteType;
use App\Form\Admin\Team\MemberAdherentAutocompleteType;
use App\Form\Audience\AudienceBackupType;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\ModelAutocompleteType;
use Sonata\DoctrineORMAdminBundle\Filter\DateRangeFilter;
use Sonata\DoctrineORMAdminBundle\Filter\ModelAutocompleteFilter;
use Sonata\Form\Type\DatePickerType;
use Sonata\Form\Type\DateRangePickerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Security\Core\Security;

class CampaignAdmin extends AbstractAdmin
{
    private $security;

    public function __construct($code, $class, $baseControllerName = null, Security $security)
    {
        parent::__construct($code, $class, $baseControllerName);

        $this->security = $security;
    }

    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->with('Informations ⚙️')
                ->add('title', TextType::class, [
                    'label' => 'Nom',
                ])
                ->add('goal', NumberType::class, [
                    'label' => 'Objectif',
                ])
                ->add('finishAt', DatePickerType::class, [
                    'label' => 'Date de fin',
                    'error_bubbling' => true,
                    'attr' => ['class' => 'width-140'],
                ])
                ->add('team', ModelAutocompleteType::class, [
                    'label' => 'Équipe',
                    'property' => [
                        'name',
                    ],
                ])
            ->end()
            ->with('Filtres')
                ->add('audience', AudienceBackupType::class, ['label' => false])
            ->end()
        ;

        $formMapper->get('audience')->add('zones', AdminZoneAutocompleteType::class, [
            'required' => false,
            'multiple' => true,
            'model_manager' => $this->getModelManager(),
            'admin_code' => AudienceAdmin::SERVICE_CODE,
        ]);
    }

    protected function configureDatagridFilters(DatagridMapper $filter)
    {
        $filter
            ->add('title', null, [
                'label' => 'Nom',
                'show_filter' => true,
            ])
            ->add('team', ModelAutocompleteFilter::class, [
                'label' => 'Équipe',
                'show_filter' => true,
                'field_options' => [
                    'property' => [
                        'name',
                    ],
                ],
            ])
            ->add('team.members.adherent', ModelAutocompleteFilter::class, [
                'label' => 'Participant',
                'show_filter' => true,
                'field_type' => MemberAdherentAutocompleteType::class,
            ])
            ->add('finishAt', DateRangeFilter::class, [
                'label' => 'Date de fin',
                'field_type' => DateRangePickerType::class,
            ])
        ;
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('title', null, [
                'label' => 'Nom',
            ])
            ->add('team', null, [
                'label' => 'Équipe',
            ])
            ->add('goal', null, [
                'label' => 'Objectif',
            ])
            ->add('finishAt', null, [
                'label' => 'Date de fin',
            ])
            ->add('_action', null, [
                'virtual_field' => true,
                'actions' => [
                    'edit' => [],
                    'delete' => [],
                ],
            ])
        ;
    }

    /**
     * @param Team $object
     */
    public function prePersist($object)
    {
        /** @var Administrator $administrator */
        $administrator = $this->security->getUser();

        $object->setAdministrator($administrator);
    }
}
