<?php

namespace AppBundle\Admin;

use AppBundle\Form\Admin\JecouteAdminSurveyQuestionFormType;
use AppBundle\Form\Jecoute\SurveyFormType;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\DoctrineORMAdminBundle\Filter\ModelAutocompleteFilter;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Translation\TranslatorInterface;

class JecouteLocalSurveyAdmin extends AbstractAdmin
{
    /** @var TranslatorInterface */
    protected $translator;

    protected $datagridValues = [
        '_page' => 1,
        '_per_page' => 32,
        '_sort_order' => 'DESC',
        '_sort_by' => 'createdAt',
    ];

    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->with('Questionnaire', ['class' => 'col-md-6'])
                ->add('name', TextType::class, [
                    'filter_emojis' => true,
                    'label' => 'Nom du questionnaire',
                ])
                ->add('concernedAreaChoice', ChoiceType::class, [
                    'choices' => SurveyFormType::concernedAreaChoices,
                    'expanded' => true,
                    'mapped' => false,
                    'label' => 'Zone concernée',
                ])
                ->add('city', TextType::class, [
                    'filter_emojis' => true,
                    'required' => false,
                    'label' => 'Ville',
                ])
                ->add('questions', CollectionType::class, [
                    'entry_type' => JecouteAdminSurveyQuestionFormType::class,
                    'required' => false,
                    'label' => 'Questions',
                    'allow_add' => true,
                    'allow_delete' => true,
                    'by_reference' => false,
                ])
                ->add('published', CheckboxType::class, [
                    'label' => 'Publié',
                    'required' => false,
                ])
            ->end()
        ;

        $formMapper->getFormBuilder()
            ->addEventListener(FormEvents::POST_SET_DATA, [$this, 'postSetData'])
            ->addEventListener(FormEvents::SUBMIT, [$this, 'validateCityByConcernedAreaChoice'])
        ;
    }

    public function postSetData(FormEvent $event): void
    {
        $form = $event->getForm();

        if ($this->getSubject()->getCity()) {
            $form->get('concernedAreaChoice')->setData(SurveyFormType::CITY_CHOICE);
        } else {
            $form->get('concernedAreaChoice')->setData(SurveyFormType::DEPARTMENT_CHOICE);
        }
    }

    public function validateCityByConcernedAreaChoice(FormEvent $event): void
    {
        $form = $event->getForm();

        if (null === $this->getSubject()->getCity() &&
            SurveyFormType::CITY_CHOICE === $form->get('concernedAreaChoice')->getData()) {
            $form->get('city')->addError(new FormError($this->trans('survey.city.required')));
        }
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('name')
            ->add('author.lastName', null, [
                'label' => "Nom de l'auteur",
                'show_filter' => true,
            ])
            ->add('author.firstName', null, [
                'label' => "Prénom de l'auteur",
                'show_filter' => true,
            ])
            ->add('author.referentTags', ModelAutocompleteFilter::class, [
                'label' => 'Zones',
                'show_filter' => true,
                'field_options' => [
                    'property' => 'name',
                    'minimum_input_length' => 2,
                ],
            ])
        ;
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('name', null, [
                'label' => 'Nom',
            ])
            ->add('author', null, [
                'label' => 'Auteur',
            ])
            ->add('getQuestionsCount', null, [
                'label' => 'Nombre de questions',
            ])
            ->add('author.referentTags', null, [
                'label' => 'Zone',
            ])
            ->add('city', null, [
                'label' => 'Ville',
            ])
            ->add('published', null, [
                'label' => 'Publié',
                'editable' => true,
            ])
            ->add('_action', null, [
                'actions' => [
                    'edit' => [],
                    'delete' => [],
                ],
            ])
        ;
    }

    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->remove('create');
    }

    /**
     * @required
     */
    public function setTranslator(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }
}
