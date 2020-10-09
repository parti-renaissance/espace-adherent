<?php

namespace App\Form\AdherentMessage;

use App\Entity\AdherentMessage\Filter\ReferentInstancesFilter;
use App\Form\ManagedPoliticalCommitteeChoiceType;
use App\Form\ManagedTerritorialCouncilChoiceType;
use App\Form\TerritorialCouncil\PoliticalCommitteeQualityChoiceType;
use App\Form\TerritorialCouncil\TerritorialCouncilQualityChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReferentInstancesFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('territorialCouncil', ManagedTerritorialCouncilChoiceType::class, [
                'required' => false,
                'placeholder' => 'Choisissez un conseil territorial',
            ])
            ->add('territorialCouncilQualities', TerritorialCouncilQualityChoiceType::class, [
                'required' => false,
                'multiple' => true,
                'mapped' => false,
            ])
            ->add('politicalCommittee', ManagedPoliticalCommitteeChoiceType::class, [
                'required' => false,
                'placeholder' => 'Choisissez un comité politique',
            ])
            ->add('politicalCommitteeQualities', PoliticalCommitteeQualityChoiceType::class, [
                'required' => false,
                'multiple' => true,
                'mapped' => false,
            ])
            ->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
                $form = $event->getForm();

                // remove political committee fields if choiceList is empty
                if (!$form->get('politicalCommittee')->getConfig()->getAttribute('choice_list')->getChoices()) {
                    $form
                        ->remove('politicalCommittee')
                        ->remove('politicalCommitteeQualities')
                    ;
                }
            })
            ->addEventListener(FormEvents::POST_SET_DATA, function (FormEvent $event) {
                /** @var ReferentInstancesFilter $data */
                $data = $event->getData();
                $form = $event->getForm();

                if ($data->getTerritorialCouncil()) {
                    $form->get('territorialCouncilQualities')->setData($data->getQualities());
                } elseif ($data->getPoliticalCommittee()) {
                    $form->get('politicalCommitteeQualities')->setData($data->getQualities());
                }
            })
            ->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
                $data = $event->getData();
                $form = $event->getForm();
                /** @var ReferentInstancesFilter $model */
                $model = $form->getData();

                if (!empty($data['territorialCouncil'])) {
                    $model->setQualities($data['territorialCouncilQualities'] ?? []);
                } elseif (!empty($data['politicalCommittee'])) {
                    $model->setQualities($data['politicalCommitteeQualities'] ?? []);
                }
            })
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ReferentInstancesFilter::class,
        ]);
    }
}
