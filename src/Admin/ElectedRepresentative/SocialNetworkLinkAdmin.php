<?php

declare(strict_types=1);

namespace App\Admin\ElectedRepresentative;

use App\Entity\ElectedRepresentative\SocialLinkTypeEnum;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;

class SocialNetworkLinkAdmin extends AbstractAdmin
{
    protected function configureRoutes(RouteCollectionInterface $collection): void
    {
        $collection->clearExcept(['create', 'edit', 'delete']);
    }

    protected function configureFormFields(FormMapper $form): void
    {
        $form
            ->add('type', ChoiceType::class, [
                'choices' => SocialLinkTypeEnum::toArray(),
                'attr' => ['class' => 'col-md-3'],
            ])
            ->add('url', UrlType::class)
        ;
    }
}
