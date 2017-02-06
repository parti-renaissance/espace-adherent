<?php

namespace AppBundle\Form;

use AppBundle\Committee\CommitteeCommand;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CommitteeCommandType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class)
            ->add('description', TextareaType::class)
            ->add('address', AddressType::class)
            ->add('facebookPageUrl', UrlType::class, [
                'required' => false,
                'default_protocol' => null,
            ])
            ->add('twitterNickname', TextType::class, [
                'required' => false,
            ])
            ->add('googlePlusPageUrl', UrlType::class, [
                'required' => false,
                'default_protocol' => null,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => CommitteeCommand::class,
        ]);
    }

    public function getBlockPrefix()
    {
        return 'committee';
    }
}
