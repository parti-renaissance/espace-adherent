<?php

namespace AppBundle\Admin\ApplicationRequest;

use AppBundle\Entity\ApplicationRequest\RunningMateRequest;
use AppBundle\Entity\ApplicationRequest\Theme;
use League\Flysystem\Filesystem;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\Form\Type\BooleanType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

class RunningMateRequestAdmin extends AbstractApplicationRequestAdmin
{
    private $storage;

    protected function configureListFields(ListMapper $listMapper)
    {
        parent::configureListFields($listMapper);

        $listMapper
            ->remove('_action')
            ->add('_action', null, [
                'actions' => [
                    'curriculum' => [
                        'template' => 'admin/running_mate/_action_curriculum.html.twig',
                    ],
                    'edit' => [],
                    'delete' => [],
                ],
            ])
        ;
    }

    protected function configureFormFields(FormMapper $formMapper)
    {
        parent::configureFormFields($formMapper);

        $formMapper
            ->with('Candidature')
                ->add('profession', null, [
                    'label' => 'Quelle est votre profession ?',
                ])
                ->add('favoriteThemes', EntityType::class, [
                    'label' => 'Vos thématique(s) de prédilection',
                    'class' => Theme::class,
                    'multiple' => true,
                ])
                ->add('customFavoriteTheme', null, [
                    'label' => 'Autre(s) thématique(s) de prédilection',
                ])
                ->add('favoriteThemeDetails', null, [
                    'label' => 'Pourquoi avez-vous choisi cette thématique ?',
                ])
                ->add('removeCurriculum', CheckboxType::class, [
                    'label' => 'Supprimer le CV ?',
                    'required' => false,
                ])
                ->add('isLocalAssociationMember', BooleanType::class, [
                    'label' => 'Êtes-vous engagé(e) dans une/des association(s) locale(s) ?',
                ])
                ->add('localAssociationDomain', null, [
                    'label' => 'Si oui, n\'hésitez pas à préciser',
                ])
                ->add('isPoliticalActivist', BooleanType::class, [
                    'label' => 'Avez-vous déjà eu un engagement militant ?',
                ])
                ->add('politicalActivistDetails', null, [
                    'label' => 'Si oui, n\'hésitez pas à préciser',
                ])
                ->add('isPreviousElectedOfficial', BooleanType::class, [
                    'label' => 'Avez-vous déjà exercé un mandat ?',
                ])
                ->add('previousElectedOfficialDetails', null, [
                    'label' => 'Si oui, précisez',
                ])
                ->add('projectDetails', null, [
                    'label' => 'Quel projet pour votre commune souhaiteriez-vous contribuer à porter ?',
                ])
                ->add('professionalAssets', null, [
                    'label' => 'Quel sont les atouts de votre parcours professionnel ?',
                ])
            ->end()
        ;
    }

    /**
     * @param RunningMateRequest $runningMateRequest
     */
    public function preUpdate($runningMateRequest)
    {
        parent::preUpdate($runningMateRequest);

        if ($runningMateRequest->getRemoveCurriculum()) {
            $this->storage->delete($runningMateRequest->getPathWithDirectory());
            $runningMateRequest->removeCurriculumName();
        }
    }

    /**
     * @param RunningMateRequest $runningMateRequest
     */
    public function postRemove($runningMateRequest)
    {
        parent::postRemove($runningMateRequest);

        if ($this->storage->has($runningMateRequest->getPathWithDirectory())) {
            $this->storage->delete($runningMateRequest->getPathWithDirectory());
        }
    }

    public function setStorage(Filesystem $storage): void
    {
        $this->storage = $storage;
    }

    public function getExportFields()
    {
        return array_merge(parent::getExportFields(), [
            'Profession' => 'profession',
            'Thématique(s) de prédilection' => 'getFavoriteThemesAsString',
            'Détails sur thématique(s) choisie(s)' => 'favoriteThemeDetails',
            'Engagé dans une/des association(s) locale(s) ?' => 'isLocalAssociationMemberAsString',
            'Précisions sur l\'engagement dans une/des association(s)' => 'localAssociationDomain',
            'Engagement militant ?' => 'isPoliticalActivistAsString',
            'Précisions sur l\'engagement militant' => 'politicalActivistDetails',
            'Déjà exercé un mandat ?' => 'isPreviousElectedOfficialAsString',
            'Précisions sur le(s) mandat(s)' => 'previousElectedOfficialDetails',
            'Projet(s) pour la commune' => 'projectDetails',
            'Atouts professionels' => 'professionalAssets',
        ]);
    }
}
