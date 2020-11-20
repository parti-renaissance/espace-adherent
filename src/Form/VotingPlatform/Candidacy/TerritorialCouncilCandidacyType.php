<?php

namespace App\Form\VotingPlatform\Candidacy;

use App\Entity\TerritorialCouncil\Candidacy;
use App\Form\DoubleNewlineTextareaType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class TerritorialCouncilCandidacyType extends AbstractType
{
    public function getParent()
    {
        return BaseCandidacyBiographyType::class;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('biography', DoubleNewlineTextareaType::class, [
                'with_character_count' => true,
                'attr' => ['maxlength' => 500],
                'constraints' => [new NotBlank(), new Length(['max' => 500])],
                'filter_emojis' => true,
            ])
            ->add('faithStatement', DoubleNewlineTextareaType::class, [
                'with_character_count' => true,
                'attr' => ['maxlength' => 2000],
                'constraints' => [new NotBlank(), new Length(['max' => 2000])],
                'filter_emojis' => true,
            ])
            ->add('isPublicFaithStatement', CheckboxType::class, [
                'required' => false,
            ])
        ;

        if ($options['with_accept']) {
            $builder
                ->add('accept', CheckboxType::class, [
                    'constraints' => [new IsTrue(['message' => 'Vous devez cocher la case pour continuer'])],
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
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class' => Candidacy::class,
                'with_accept' => true,
            ])
            ->setDefined('with_accept')
            ->setAllowedTypes('with_accept', ['bool'])
        ;
    }
}
