<?php

namespace AppBundle\Admin\ElectedRepresentative;

use AppBundle\Entity\ElectedRepresentative\SocialLinkTypeEnum;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;

class SocialNetworkLinkAdmin extends AbstractAdmin
{
    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->clearExcept(['create', 'edit', 'delete']);
    }

    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->add('type', ChoiceType::class, [
                'choices' => SocialLinkTypeEnum::toArray(),
                'attr' => ['class' => 'col-md-3'],
            ])
            ->add('url', UrlType::class)
        ;
    }
}
