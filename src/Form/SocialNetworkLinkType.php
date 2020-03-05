<?php

namespace AppBundle\Form;

use AppBundle\Entity\ElectedRepresentative\SocialLinkTypeEnum;
use AppBundle\Entity\ElectedRepresentative\SocialNetworkLink;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SocialNetworkLinkType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('type', ChoiceType::class, [
                'choices' => SocialLinkTypeEnum::toArray(),
            ])
            ->add('url', UrlType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => SocialNetworkLink::class,
        ]);
    }
}
