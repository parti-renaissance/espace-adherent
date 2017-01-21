<?php

namespace AppBundle\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;

class NewsletterSubscriptionAdmin extends AbstractAdmin
{
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('email', null, [
                'label' => 'Adresse e-mail',
            ])
            ->add('postalCode', null, [
                'label' => 'Code postal',
            ])
            ->add('clientIp', null, [
                'label' => 'IP du client',
            ])
            ->add('createdAt', null, [
                'label' => 'Date de création',
            ]);
    }
}
