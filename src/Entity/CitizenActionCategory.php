<?php

namespace AppBundle\Entity;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\CitizenActionCategoryRepository")
 * @ORM\Table(
 *   name="citizen_action_categories",
 *   uniqueConstraints={
 *     @ORM\UniqueConstraint(name="citizen_action_category_name_unique", columns="name")
 *   }
 * )
 *
 * @UniqueEntity("name")
 *
 * @Algolia\Index
 */
class CitizenActionCategory extends BaseEventCategory
{
}
