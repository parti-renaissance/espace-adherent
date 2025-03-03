<?php

namespace App\Form\Audience;

use App\Adherent\AdherentRoleEnum;
use App\Entity\Audience\AudienceSnapshot;
use App\Form\DatePickerType;
use App\Form\GenderType;
use Sonata\Form\Type\BooleanType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AudienceSnapshotType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('gender', GenderType::class, ['label' => 'Civilité', 'required' => false, 'placeholder' => ''])
            ->add('firstName', TextType::class, ['label' => 'Prénom', 'required' => false])
            ->add('lastName', TextType::class, ['label' => 'Nom', 'required' => false])
            ->add('ageMin', IntegerType::class, ['label' => 'Âge min', 'required' => false, 'attr' => ['min' => 1]])
            ->add('ageMax', IntegerType::class, ['label' => 'Âge max', 'required' => false, 'attr' => ['min' => 1]])
            ->add('registeredSince', DatePickerType::class, ['label' => 'Date d\'adhésion (depuis le)', 'required' => false])
            ->add('registeredUntil', DatePickerType::class, ['label' => 'Date d\'adhésion (au)', 'required' => false])
            ->add('isCertified', BooleanType::class, ['transform' => true, 'label' => 'Certifié', 'required' => false])
            ->add('isCommitteeMember', BooleanType::class, ['transform' => true, 'label' => 'Membre de comité', 'required' => false])
            ->add('hasSmsSubscription', BooleanType::class, ['transform' => true, 'label' => 'Abonné aux SMS', 'required' => false])
            ->add('roles', ChoiceType::class, [
                'label' => 'Rôles',
                'required' => false,
                'multiple' => true,
                'choices' => [
                    AdherentRoleEnum::COMMITTEE_SUPERVISOR => AdherentRoleEnum::COMMITTEE_SUPERVISOR,
                    AdherentRoleEnum::COMMITTEE_PROVISIONAL_SUPERVISOR => AdherentRoleEnum::COMMITTEE_PROVISIONAL_SUPERVISOR,
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => AudienceSnapshot::class]);
    }
}
