<?php

namespace AppBundle\Entity;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="jecoute_managed_areas")
 * @ORM\Entity
 *
 * @Algolia\Index(autoIndex=false)
 */
class JecouteManagedArea extends ManagedArea
{
}
