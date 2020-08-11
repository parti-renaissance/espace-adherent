<?php

namespace App\Form\VotingPlatform\Candidacy;

use App\Entity\TerritorialCouncil\Candidacy;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TerritorialCouncilCandidacyType extends AbstractType
{
    public function getParent()
    {
        return BaseCandidacyBiographyType::class;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('image', FileType::class)
            ->add('biography', TextareaType::class, [
                'with_character_count' => true,
                'attr' => ['maxlength' => 500],
                'filter_emojis' => true,
            ])
            ->add('faithStatement', TextareaType::class, [
                'with_character_count' => true,
                'attr' => ['maxlength' => 2000],
                'filter_emojis' => true,
            ])
            ->add('isPublicFaithStatement', CheckboxType::class, [
                'required' => false,
            ])
            ->add('accept', CheckboxType::class, [
                'mapped' => false,
                'required' => true,
            ])
            ->addEventListener(FormEvents::POST_SET_DATA, function (FormEvent $event) {
                $candidacy = $event->getData();
                // Pre check `Accept` checkbox if update of candidature
                if ($candidacy instanceof Candidacy && $candidacy->getId()) {
                    $event->getForm()->get('accept')->setData(true);
                }
            })
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Candidacy::class,
        ]);
    }
}
