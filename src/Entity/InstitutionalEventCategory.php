<?php

namespace App\Entity;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass="App\Repository\InstitutionalEventCategoryRepository")
 * @ORM\Table(
 *     name="institutional_events_categories",
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(name="institutional_event_category_name_unique", columns="name"),
 *         @ORM\UniqueConstraint(name="institutional_event_slug_unique", columns="slug")
 *     }
 * )
 *
 * @UniqueEntity("name")
 *
 * @Algolia\Index(autoIndex=false)
 */
class InstitutionalEventCategory extends BaseEventCategory
{
}
