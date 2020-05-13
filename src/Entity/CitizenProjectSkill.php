<?php

namespace App\Entity;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CitizenProjectSkillRepository")
 * @ORM\Table(
 *     name="citizen_project_skills",
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(name="citizen_project_skill_slug_unique", columns="slug"),
 *         @ORM\UniqueConstraint(name="citizen_project_skill_name_unique", columns="name")
 *     }
 * )
 *
 * @UniqueEntity("name")
 *
 * @Algolia\Index(autoIndex=false)
 */
class CitizenProjectSkill extends BaseSkill
{
    public function __construct(?string $name = null)
    {
        parent::__construct($name);
    }
}
