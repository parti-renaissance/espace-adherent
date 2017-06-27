<?php

namespace AppBundle\Form;

use AppBundle\Entity\MemberSummary\Language;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\LanguageType as CoreLanguageType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LanguageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('code', CoreLanguageType::class)
            ->add('level', LanguageLevelChoiceType::class)
            ->add('submit', SubmitType::class, [
                'label' => $builder->getData() ? 'Éditer' : 'Ajouter',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class' => Language::class,
            ])
        ;
    }
}
