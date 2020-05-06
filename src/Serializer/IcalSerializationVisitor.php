<?php

namespace App\Serializer;

use JMS\Serializer\GenericSerializationVisitor;
use Sabre\VObject\Component\VCalendar;

class IcalSerializationVisitor extends GenericSerializationVisitor
{
    /**
     * @return string|null
     */
    public function getResult()
    {
        if (!$root = $this->getRoot()) {
            return;
        }

        return (new VCalendar($root))->serialize();
    }
}
