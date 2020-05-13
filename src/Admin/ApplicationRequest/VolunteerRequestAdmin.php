<?php

namespace App\Admin\ApplicationRequest;

use App\Entity\ApplicationRequest\TechnicalSkill;
use App\Entity\ApplicationRequest\Theme;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\Form\Type\BooleanType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class VolunteerRequestAdmin extends AbstractApplicationRequestAdmin
{
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
                ->add('technicalSkills', EntityType::class, [
                    'label' => 'Disposez-vous de compétences techniques spécifiques ?',
                    'class' => TechnicalSkill::class,
                    'multiple' => true,
                ])
                ->add('customTechnicalSkills', null, [
                    'label' => 'Autres compétences techniques',
                ])
                ->add('isPreviousCampaignMember', BooleanType::class, [
                    'label' => 'Avez-vous déjà participé à une campagne ?',
                ])
                ->add('previousCampaignDetails', null, [
                    'label' => 'Si oui, n\'hésitez pas à préciser',
                ])
                ->add('shareAssociativeCommitment', BooleanType::class, [
                    'label' => 'Souhaitez-vous nous faire part de vos engagements associatifs et/ou militants ?',
                ])
                ->add('associativeCommitmentDetails', null, [
                    'label' => 'Si oui, n\'hésitez pas à préciser',
                ])
            ->end()
        ;
    }

    public function getExportFields()
    {
        return array_merge(parent::getExportFields(), [
            'Profession' => 'profession',
            'Thématique(s) de prédilection' => 'getFavoriteThemesAsString',
            'Compétences techniques' => 'getTechnicalSkillsAsString',
            'Déjà participé à une campagne ?' => 'isPreviousCampaignMemberAsString',
            'Précisions sur la dernière campagne' => 'previousCampaignDetails',
            'Souhaite faire part de ses engagements' => 'shareAssociativeCommitmentAsString',
            'Précisions sur les engagements' => 'associativeCommitmentDetails',
        ]);
    }
}
