<?php

namespace AppBundle\Admin\Election;

use AppBundle\Entity\Election\CityCard;
use AppBundle\Form\Admin\Election\CityCandidateType;
use AppBundle\Form\Admin\Election\CityPrevisionType;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class CityCardAdmin extends AbstractCityCardAdmin
{
    protected function configureFormFields(FormMapper $form)
    {
        parent::configureFormFields($form);

        $form
            ->add('priority', ChoiceType::class, [
                'label' => 'Priorité',
                'required' => false,
                'choices' => CityCard::PRIORITY_CHOICES,
                'choice_label' => function (string $choice) {
                    return "election.city_card.priority.$choice";
                },
            ])
            ->add('risk', CheckboxType::class, [
                'label' => 'Risque',
                'required' => false,
            ])
            ->add('firstCandidate', CityCandidateType::class, [
                'required' => false,
            ])
            ->add('candidateOptionPrevision', CityPrevisionType::class, [
                'label' => 'Option candidat',
                'required' => false,
                'disabled' => true,
            ])
            ->add('preparationPrevision', CityPrevisionType::class, [
                'label' => 'Cohérence territoriale',
                'required' => false,
                'disabled' => true,
            ])
            ->add('thirdOptionPrevision', CityPrevisionType::class, [
                'label' => 'Troisième option',
                'required' => false,
                'disabled' => true,
            ])
            ->add('candidatePrevision', CityPrevisionType::class, [
                'label' => 'Position candidat',
                'required' => false,
            ])
            ->add('nationalPrevision', CityPrevisionType::class, [
                'label' => 'Arbitrage national',
                'required' => false,
            ])
            ->add('partners', 'sonata_type_collection', [
                'by_reference' => false,
            ], [
                'edit' => 'inline',
                'inline' => 'table',
                'admin_code' => 'app.admin.election_city_card_partner',
            ])
            ->add('contacts', 'sonata_type_collection', [
                'by_reference' => false,
            ], [
                'edit' => 'inline',
                'inline' => 'table',
                'admin_code' => 'app.admin.election_city_card_contact',
            ])
        ;
    }

    protected function configureListFields(ListMapper $list)
    {
        parent::configureListFields($list);

        $list
            ->add('candidateOptionPrevision', null, [
                'label' => 'Option candidat',
                'template' => 'admin/election/city_card/_list_prevision.html.twig',
            ])
            ->add('preparationPrevision', null, [
                'label' => 'Cohérence territoriale',
                'template' => 'admin/election/city_card/_list_prevision.html.twig',
            ])
            ->add('nationalPrevision', null, [
                'label' => 'Schéma arbitré',
                'template' => 'admin/election/city_card/_list_prevision.html.twig',
            ])
            ->add('results', null, [
                'label' => 'Résultats',
                'virtual_field' => true,
                'template' => 'admin/election/city_card/_list_results.html.twig',
            ])
            ->add('allContactsDone', null, [
                'label' => 'Personnes contactées',
                'virtual_field' => true,
                'template' => 'admin/election/city_card/_list_all_contacts_done.html.twig',
            ])
            ->reorder([
                'city.name',
                'city.inseeCode',
                'city.department',
                'city.department.region',
                'priority',
                'candidateOptionPrevision',
                'preparationPrevision',
                'nationalPrevision',
                'results',
                'allContactsDone',
                '_action',
            ])
        ;
    }

    public function getTemplate($name)
    {
        if ('list' === $name) {
            return 'admin/city_card/list.html.twig';
        }

        return parent::getTemplate($name);
    }

    public function getFilterParameters()
    {
        if ($this->hasRequest()) {
            if ('reset' !== $this->request->query->get('filters')) {
                $this->datagridValues = array_merge([
                        'priority' => [
                            'value' => CityCard::PRIORITY_HIGH,
                        ],
                    ],
                    $this->datagridValues
                );
            }
        }

        return parent::getFilterParameters();
    }
}
