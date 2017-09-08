<?php

namespace AppBundle\Form;

use AppBundle\Membership\UnregistrationCommand;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UnregistrationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('comment', TextType::class, [
                'required' => false,
                'filter_emojis' => true,
            ])
            ->add('reasons', UnregistrationReasonsChoiceType::class)
            ->add('word', UnregisterType::class, [
                'mapped' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => UnregistrationCommand::class,
        ]);
    }
}
