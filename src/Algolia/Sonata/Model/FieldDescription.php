<?php

namespace App\Algolia\Sonata\Model;

use Sonata\AdminBundle\FieldDescription\BaseFieldDescription;

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

    public function setAssociationMapping(array $associationMapping): void
    {
    }

    public function getTargetEntity()
    {
    }

    public function setFieldMapping(array $fieldMapping): void
    {
    }

    public function setParentAssociationMappings(array $parentAssociationMappings): void
    {
    }

    public function isIdentifier(): bool
    {
        return false;
    }

    public function getTargetModel(): ?string
    {
        return null;
    }

    public function describesSingleValuedAssociation(): bool
    {
        return false;
    }

    public function describesCollectionValuedAssociation(): bool
    {
        return false;
    }
}
