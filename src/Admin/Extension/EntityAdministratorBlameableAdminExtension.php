<?php

namespace App\Admin\Extension;

use App\Admin\ReorderableAdminInterface;
use App\Entity\Administrator;
use App\Entity\EntityAdministratorBlameableInterface;
use Sonata\AdminBundle\Admin\AbstractAdminExtension;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Symfony\Component\Security\Core\Security;

class EntityAdministratorBlameableAdminExtension extends AbstractAdminExtension
{
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('createdByAdministrator', null, [
                'label' => 'Créé par',
            ])
            ->add('updatedByAdministrator', null, [
                'label' => 'Modifié par',
            ])
        ;
    }

    public function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('_blame', null, [
                'label' => 'Auteur',
                'virtual_field' => true,
                'template' => 'admin/_list_administrator_blameable.html.twig',
            ])
        ;

        $keys = $listMapper->keys();
        $admin = $listMapper->getAdmin();

        foreach ($admin instanceof ReorderableAdminInterface ? array_merge($admin->getListMapperEndColumns(), ['_action']) : ['_action'] as $column) {
            if (false !== $actionKey = array_search($column, $keys)) {
                unset($keys[$actionKey]);
                $keys[] = $column;
            }
        }

        $listMapper->reorder($keys);
    }

    /**
     * @param EntityAdministratorBlameableInterface $object
     */
    public function prePersist(AdminInterface $admin, $object)
    {
        $administrator = $this->getAdministrator();

        $object->setCreatedByAdministrator($administrator);
    }

    /**
     * @param EntityAdministratorBlameableInterface $object
     */
    public function preUpdate(AdminInterface $admin, $object)
    {
        $object->setUpdatedByAdministrator($this->getAdministrator());
    }

    private function getAdministrator(): Administrator
    {
        return $this->security->getUser();
    }
}
