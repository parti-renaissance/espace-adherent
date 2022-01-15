<?php

namespace App\Form;

use App\Entity\AbstractFeedItem;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FeedItemType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('content', PurifiedTextareaType::class, [
                'label' => false,
                'attr' => [
                    'maxlength' => 6000,
                    'placeholder' => 'Écrivez ici votre message',
                ],
                'purify_html_profile' => 'enrich_content',
                'with_character_count' => true,
            ])
            ->add('save', SubmitType::class, ['label' => 'Sauvegarder'])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => AbstractFeedItem::class,
        ]);
    }
}
