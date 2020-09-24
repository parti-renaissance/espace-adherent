<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CitizenActionCategoryRepository")
 * @ORM\Table(
 *     name="citizen_action_categories",
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(name="citizen_action_category_name_unique", columns="name"),
 *         @ORM\UniqueConstraint(name="citizen_action_category_slug_unique", columns="slug")
 *     }
 * )
 *
 * @UniqueEntity("name")
 */
class CitizenActionCategory extends BaseEventCategory
{
}
