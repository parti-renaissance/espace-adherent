<?php

namespace App\Form;

use App\Entity\CommitteeFeedItem;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CommitteeFeedItemMessageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->remove('subject')
            ->remove('sendNotification')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CommitteeFeedItem::class,
        ]);
    }

    public function getParent(): string
    {
        return CommitteeFeedMessageType::class;
    }
}
