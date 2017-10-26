<?php

namespace AppBundle\Entity;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\MoocEventCategoryRepository")
 * @ORM\Table(
 *   name="mooc_event_categories",
 *   uniqueConstraints={
 *     @ORM\UniqueConstraint(name="mooc_event_category_name_unique", columns="name")
 *   }
 * )
 *
 * @UniqueEntity("name")
 *
 * @Algolia\Index
 */
class MoocEventCategory extends BaseEventCategory
{
}
