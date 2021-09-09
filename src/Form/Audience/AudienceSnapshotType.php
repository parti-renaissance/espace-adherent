<?php

namespace App\Form\Audience;

use App\Entity\Audience\AudienceSnapshot;
use App\Form\DatePickerType;
use App\Form\GenderType;
use Sonata\Form\Type\BooleanType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AudienceSnapshotType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('gender', GenderType::class, ['label' => 'Genre', 'required' => false, 'placeholder' => ''])
            ->add('firstName', TextType::class, ['label' => 'Prénom', 'required' => false])
            ->add('lastName', TextType::class, ['label' => 'Nom', 'required' => false])
            ->add('ageMin', IntegerType::class, ['label' => 'Âge min', 'required' => false, 'attr' => ['min' => 1]])
            ->add('ageMax', IntegerType::class, ['label' => 'Âge max', 'required' => false, 'attr' => ['min' => 1]])
            ->add('registeredSince', DatePickerType::class, ['label' => 'Date d\'adhésion (depuis le)', 'required' => false])
            ->add('registeredUntil', DatePickerType::class, ['label' => 'Date d\'adhésion (au)', 'required' => false])
            ->add('isCertified', BooleanType::class, ['label' => 'Certifié', 'required' => false])
            ->add('isCommitteeMember', BooleanType::class, ['label' => 'Membre de comité', 'required' => false])
            ->add('hasSmsSubscription', BooleanType::class, ['label' => 'Abonné aux SMS', 'required' => false])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(['data_class' => AudienceSnapshot::class]);
    }
}
