<?php

namespace AppBundle\Serializer\Visitor;

use JMS\Serializer\GenericSerializationVisitor;
use Sabre\VObject\Component\VCalendar;

class IcalSerializationVisitor extends GenericSerializationVisitor
{
    /**
     * @return null|string
     */
    public function getResult()
    {
        if (!$root = $this->getRoot()) {
            return;
        }

        return (new VCalendar($root))->serialize();
    }
}
