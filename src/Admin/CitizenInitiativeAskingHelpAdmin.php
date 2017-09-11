<?php

namespace AppBundle\Admin;

use AppBundle\Form\{
    EventListener\SkillListener, SkillType
};
use AppBundle\Repository\SkillRepository;
use Sonata\AdminBundle\{
    Admin\AbstractAdmin, Datagrid\ListMapper, Form\FormMapper
};
use Sonata\CoreBundle\Form\Type\BooleanType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class CitizenInitiativeAskingHelpAdmin extends AbstractAdmin
{
    protected $baseRouteName = 'admin_app_asking_help_citizeninitiative';
    protected $baseRoutePattern = 'citizeninitiative_asking_help';

    protected $datagridValues = [
        '_page' => 1,
        '_per_page' => 32,
        '_sort_order' => 'ASC',
        '_sort_by' => 'name',
    ];

    private $skillRepository;

    /**
     * CitizenInitiativeAskingHelpAdmin constructor.
     * @param $code
     * @param $class
     * @param $baseControllerName
     * @param SkillRepository $skillRepository
     */
    public function __construct($code, $class, $baseControllerName, SkillRepository $skillRepository)
    {
        parent::__construct($code, $class, $baseControllerName);

        $this->skillRepository = $skillRepository;
    }

    /**
     * @param string $context
     * @return mixed
     */
    public function createQuery($context = 'list')
    {
        $query = parent::createQuery($context);

        $query->andWhere(
            $query->expr()->orX(
                $query->expr()->eq($query->getRootAlias().'.expertAssistanceNeeded', ':expertAssistanceNeeded'),
                $query->expr()->eq($query->getRootAlias().'.coachingRequested', ':coachingRequested')
            )
        );

        $query->setParameter('expertAssistanceNeeded', BooleanType::TYPE_YES);
        $query->setParameter('coachingRequested', BooleanType::TYPE_YES);

        return $query;
    }

    public function getFormTheme()
        : array
    {
        return ['admin/citizen_initiative/admin.theme.html.twig'];
    }

    protected function configureFormFields(FormMapper $formMapper)
        : void
    {
        $formMapper
            ->add('skills', CollectionType::class, [
                'label' => 'Compétences',
                'required' => false,
                'entry_type' => SkillType::class,
                'entry_options' => ['label' => false],
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
            ]);

        $formMapper->getFormBuilder()->addEventSubscriber(new SkillListener($this->skillRepository));
    }

    protected function configureListFields(ListMapper $listMapper): void
    {
        $listMapper
            ->add('name', null, [
                'label' => 'Nom',
            ])
            ->add('organizer', null, [
                'label' => 'Organisateur',
                'template' => 'admin/event/list_organizer.html.twig',
            ])
            ->add('beginAt', null, [
                'label' => 'Date de début',
            ])
            ->add('_location', null, [
                'label' => 'Lieu',
                'virtual_field' => true,
                'template' => 'admin/event/list_location.html.twig',
            ])
            ->add('problemDescription', null, [
                'label' => 'Problème adressé',
            ])
            ->add('proposedSolution', null, [
                'label' => 'Descripton d\'initiative',
            ])
            ->add('requiredMeans', null, [
                'label' => 'Question',
            ])
            ->add('skills', null, [
                'label' => 'Compétences',
            ])
            ->add('_status', null, [
                'label' => 'Statut',
                'virtual_field' => true,
                'template' => 'admin/citizen_initiative/list_asking_help.html.twig',
            ])
            ->add('_action', null, [
                'virtual_field' => true,
                'template' => 'admin/citizen_initiative/list_asking_help_actions.html.twig',
            ])
        ;
    }
}
