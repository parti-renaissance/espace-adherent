<?php

namespace App\ElectedRepresentative;

use App\Entity\ElectedRepresentative\Mandate;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class ElectedRepresentativeMandatesOrderer
{
    public static function updateOrder(Collection $mandates): ArrayCollection
    {
        $arr = $mandates->toArray();
        usort($arr, function (Mandate $mandateA, Mandate $mandateB) {
            return $mandateA->getBeginAt() < $mandateB->getBeginAt() ? -1 : 1;
        });

        foreach ($arr as $key => $mandate) {
            $mandate->setNumber(++$key);
        }

        return new ArrayCollection($arr);
    }
}
