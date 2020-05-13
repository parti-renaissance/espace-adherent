<?php

namespace App\Entity\ReferentOrganizationalChart;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class GroupOrganizationalChartItem extends AbstractOrganizationalChartItem
{
    public function getTypeLabel(): string
    {
        return 'Groupe';
    }
}
