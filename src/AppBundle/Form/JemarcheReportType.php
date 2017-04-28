<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use AppBundle\Entity\JemarcheReport;

class JemarcheReportType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('actionType', ChoiceType::class, array(
                'choices' => array(
                    'La marche' => 'la-marche',
                    'Le porte a porte' => 'le-porte-a-porte',
                    'Le diner' => 'le-diner',
                    'La correspondance' => 'la-correspondance',
                    'L\'atelier du projet' => 'l-atelier-du-projet',
                    'L\'action qui me ressemble' => 'l-action-qui-me-ressemble',
                ),
                'attr' => array('class' => 'form--full'),
            ))
            ->add('convincedContacts', TextareaType::class, array(
                'attr' => array('class' => 'form--full', 'placeholder' => 'email1@example.com,email2@exemple.com,...'),
            ))
            ->add('almostConvincedContacts', TextareaType::class, array(
                'attr' => array('class' => 'form--full', 'placeholder' => 'email1@example.com,email2@exemple.com,...'),
            ))
            ->add('notConvincedContacts', ChoiceType::class, array(
                'choices' => array(
                    '1 personne' => '1-personne',
                    '2 personnes' => '2-personnes',
                    '3 personnes' => '3-personnes',
                    '4 personnes' => '4-personnes',
                    '5 personnes' => '5-personnes',
                    'Plus de 5 personnes' => 'plus-de-5-personnes',
                    'Aucunnes' => 'aucunnes',
                ),
                'attr' => array('class' => 'form--full'),
            ))
            ->add('publicReactions', TextAreaType::class, array(
                'attr' => array('class' => 'form--full', 'placeholder' => 'Décrivez ici les réactions des personnes avec lesquelles vous avez échangé'),
            ))
            ->add('organizer', EmailType::class, array(
                'attr' => array('class' => 'form--full', 'placeholder' => 'Saisissez ici votre email'),
            ));
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => JemarcheReport::class,
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'app_jemarchereport';
    }
}
