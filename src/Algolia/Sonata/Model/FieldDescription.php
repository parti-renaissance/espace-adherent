<?php

namespace App\Algolia\Sonata\Model;

use Sonata\AdminBundle\Admin\BaseFieldDescription;

class FieldDescription extends BaseFieldDescription
{
    public function getValue($row)
    {
        if ($this->isVirtual()) {
            return null;
        }

        $namePars = explode('.', $this->getName());

        return $this->_getValue($namePars, $row);
    }

    private function _getValue($keys, $row)
    {
        $key = array_shift($keys);

        return $key ? $this->_getValue($keys, $row[$key]) : $row;
    }

    public function setAssociationMapping($associationMapping)
    {
    }

    public function getTargetEntity()
    {
    }

    public function setFieldMapping($fieldMapping)
    {
    }

    public function setParentAssociationMappings(array $parentAssociationMappings)
    {
    }

    public function isIdentifier()
    {
    }
}
