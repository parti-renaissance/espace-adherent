<?php

namespace App\Admin\Election;

use App\Entity\Election\CityPartner;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Form\FormMapper;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class CityCardPartnerAdmin extends AbstractAdmin
{
    protected function configureFormFields(FormMapper $form)
    {
        $form
            ->add('label', TextType::class, [
                'label' => 'Label',
            ])
            ->add('consensus', ChoiceType::class, [
                'label' => 'Consensus',
                'required' => false,
                'choices' => CityPartner::CONSENSUS_CHOICES,
                'choice_label' => function (string $choice) {
                    return "election.city_partner.$choice";
                },
            ])
        ;
    }
}
