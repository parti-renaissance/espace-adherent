<?php

declare(strict_types=1);

namespace App\Admin\Extension;

use App\Entity\Administrator;
use App\Entity\EntityAdministratorBlameableInterface;
use Sonata\AdminBundle\Admin\AbstractAdminExtension;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Symfony\Bundle\SecurityBundle\Security;

class EntityAdministratorBlameableAdminExtension extends AbstractAdminExtension
{
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add('createdByAdministrator', null, [
                'label' => 'Créé par',
            ])
            ->add('updatedByAdministrator', null, [
                'label' => 'Modifié par',
            ])
        ;
    }

    public function configureListFields(ListMapper $list): void
    {
        $list
            ->add('_blame', null, [
                'label' => 'Auteur',
                'virtual_field' => true,
                'template' => 'admin/list/list_administrator_blameable.html.twig',
            ])
        ;
    }

    /**
     * @param EntityAdministratorBlameableInterface $object
     */
    public function prePersist(AdminInterface $admin, object $object): void
    {
        $administrator = $this->getAdministrator();

        $object->setCreatedByAdministrator($administrator);
    }

    /**
     * @param EntityAdministratorBlameableInterface $object
     */
    public function preUpdate(AdminInterface $admin, object $object): void
    {
        $object->setUpdatedByAdministrator($this->getAdministrator());
    }

    private function getAdministrator(): Administrator
    {
        return $this->security->getUser();
    }
}
