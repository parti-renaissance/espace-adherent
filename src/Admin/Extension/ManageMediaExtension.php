<?php

namespace AppBundle\Admin\Extension;

use AppBundle\Entity\Media;
use Sonata\AdminBundle\Admin\AbstractAdminExtension;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Form\Type\AdminType;

class ManageMediaExtension extends AbstractAdminExtension
{
    public function prePersist(AdminInterface $admin, $object)
    {
        $this->manageEmbeddedMediaAdmin($admin, $object);
    }

    public function preUpdate(AdminInterface $admin, $object)
    {
        $this->manageEmbeddedMediaAdmin($admin, $object);
    }

    private function manageEmbeddedMediaAdmin(AdminInterface $admin, $object): void
    {
        // Cycle through each field
        foreach ($admin->getFormFieldDescriptions() as $fieldName => $fieldDescription) {
            // detect embedded Admins that manage Images
            if (AdminType::class === $fieldDescription->getType() &&
                ($associationMapping = $fieldDescription->getAssociationMapping()) &&
                Media::class === $associationMapping['targetEntity']
            ) {
                $getter = 'get'.$fieldName;
                $setter = 'set'.$fieldName;

                /** @var Media $media */
                $media = $object->$getter();

                if ($media && !$media->getFile() && !$media->getId()) {
                    $object->$setter(null);
                }
            }
        }
    }
}
