<?php

namespace App\Form;

use App\Committee\Feed\CommitteeMessage;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CommitteeFeedMessageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('subject', TextType::class, [
                'label' => false,
                'attr' => [
                    'class' => 'form--full b__nudge--bottom-medium',
                    'placeholder' => 'Entrez l\'objet de votre message',
                ],
            ])
            ->add('content', PurifiedTextareaType::class, [
                'label' => false,
                'attr' => [
                    'maxlength' => 6000,
                    'placeholder' => 'Écrivez ici votre message',
                ],
                'filter_emojis' => true,
                'purifier_type' => 'enrich_content',
                'with_character_count' => true,
            ])
            ->add('published', CheckboxType::class, [
                'label' => 'Publier sur la page du comité',
                'attr' => ['class' => 'form__checkbox form__checkbox--large'],
                'required' => false,
            ])
            ->add('sendNotification', CheckboxType::class, [
                'label' => 'Envoyer le message par e-mail. Vous serez systématiquement mis(e) en copie de celui-ci',
                'attr' => ['class' => 'form__checkbox form__checkbox--large'],
                'required' => false,
            ])
            ->add('send', SubmitType::class, ['label' => 'Envoyer'])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => CommitteeMessage::class,
            'validation_groups' => function (FormInterface $form) {
                $committeeMessage = $form->getData();

                if ($committeeMessage instanceof CommitteeMessage && $committeeMessage->isSendNotification()) {
                    return ['Default', 'notification'];
                }

                return ['Default'];
            },
        ]);
    }
}
