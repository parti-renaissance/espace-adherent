<?php

namespace App\Form;

use App\Entity\CommitteeFeedItem;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CommitteeFeedItemMessageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->remove('subject')
            ->remove('sendNotification')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => CommitteeFeedItem::class,
        ]);
    }

    public function getParent()
    {
        return CommitteeFeedMessageType::class;
    }
}
