<?php

namespace AppBundle\Form;

use AppBundle\Committee\Feed\CommitteeMessage;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CommitteeFeedMessageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('content', TextareaType::class, [
            'label' => false,
            'attr' => ['placeholder' => 'Écrivez ici votre message public'],
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('data_class', CommitteeMessage::class);
    }
}
