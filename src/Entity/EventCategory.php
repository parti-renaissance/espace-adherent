<?php

namespace AppBundle\Entity;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\EventCategoryRepository")
 * @ORM\Table(
 *   name="events_categories",
 *   uniqueConstraints={
 *     @ORM\UniqueConstraint(name="event_category_name_unique", columns="name")
 *   }
 * )
 *
 * @UniqueEntity("name")
 *
 * @Algolia\Index
 */
class EventCategory extends BaseEventCategory
{
}
