<?php

namespace App\LocalElection;

use App\Entity\Geo\Zone;
use Doctrine\Common\Collections\ArrayCollection;

class LocalElectionCollection extends ArrayCollection
{
    public function getAllByRegions(): array
    {
        $regions = [];
        foreach ($this->toArray() as $election) {
            $zones = $election->getDesignation()->getZones()->first()->getParentsOfType(Zone::REGION);
            $region = reset($zones);

            if (!\array_key_exists($region->getCode(), $regions)) {
                $regions[$region->getCode()] = ['region' => $region];
            }

            $regions[$region->getCode()]['elections'][] = $election;
        }

        return $regions;
    }
}
