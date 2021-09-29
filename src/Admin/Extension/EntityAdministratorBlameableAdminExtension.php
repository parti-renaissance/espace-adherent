<?php

namespace App\Admin\Extension;

use App\Entity\Administrator;
use App\Entity\EntityAdministratorBlameableInterface;
use Sonata\AdminBundle\Admin\AbstractAdminExtension;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
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

    /**
     * @param EntityAdministratorBlameableInterface $object
     */
    public function prePersist(AdminInterface $admin, $object)
    {
        $administrator = $this->getAdministrator();

        $object->setCreatedByAdministrator($administrator);
        $object->setUpdatedByAdministrator($administrator);
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
