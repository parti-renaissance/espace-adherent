<?php

namespace App\Form;

use App\Entity\WebHook\WebHook;
use App\WebHook\Event;
use App\WebHook\Service;
use Sonata\AdminBundle\Form\Type\CollectionType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class WebHookType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('event', ChoiceType::class, [
                'placeholder' => '--',
                'choices' => Event::toArray(),
                'choice_label' => function ($value) { return $value; },
            ])
            ->add('callbacks', CollectionType::class, [
                'label' => 'Liste des callbacks',
                'entry_type' => UrlType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'error_bubbling' => false,
            ])
            ->add('service', ChoiceType::class, [
                'required' => false,
                'placeholder' => '--',
                'choices' => Service::toArray(),
                'choice_label' => function ($value) { return $value; },
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => WebHook::class,
        ]);
    }
}
