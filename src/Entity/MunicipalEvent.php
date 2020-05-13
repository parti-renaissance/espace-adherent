<?php

namespace App\Entity;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 *
 * @ApiResource(
 *     attributes={
 *         "normalization_context": {"groups": {"event_read"}},
 *         "order": {"beginAt": "DESC"},
 *     },
 *     collectionOperations={"get"},
 *     itemOperations={"get"},
 * )
 *
 * @ApiFilter(SearchFilter::class, properties={"postAddress.postalCode": "exact", "category.name": "exact"})
 *
 * @Algolia\Index(autoIndex=false)
 */
class MunicipalEvent extends Event
{
}
