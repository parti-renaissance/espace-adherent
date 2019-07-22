<?php

namespace AppBundle\Entity;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 *
 * @ApiResource(
 *     attributes={"normalization_context": {"groups": {"event_read"}}},
 *     collectionOperations={"get"},
 *     itemOperations={"get"},
 * )
 *
 * @Algolia\Index(autoIndex=false)
 */
class MunicipalEvent extends Event
{
}
