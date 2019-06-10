<?php

namespace AppBundle\Entity;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use Doctrine\ORM\Mapping as ORM;
/**
 * @ORM\Table(name="municipal_chief_areas")
 * @ORM\Entity
 *
 * @Algolia\Index(autoIndex=false)
 */
class MunicipalChiefManagedArea extends ManagedArea
{
}
