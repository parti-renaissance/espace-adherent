<?php

namespace App\Entity\Event;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass="App\Repository\InstitutionalEventCategoryRepository")
 * @ORM\Table(name="institutional_events_categories")
 *
 * @UniqueEntity("name")
 */
class InstitutionalEventCategory extends BaseEventCategory
{
}
