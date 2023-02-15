<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Serializer\Annotation\Groups;

trait EntityNameSlugTrait
{
    /**
     * @var string
     *
     * @ORM\Column
     *
     * @Groups({
     *     "adherent_committees_modal",
     *     "jecoute_region_read",
     *     "cause_read",
     *     "cause_write",
     *     "event_read",
     *     "committee_sync",
     *     "committee:list",
     *     "committee:write",
     * })
     */
    protected $name;

    /**
     * @var string
     *
     * @ORM\Column
     */
    protected $canonicalName;

    /**
     * @var string
     *
     * @ORM\Column
     *
     * @Gedmo\Slug(fields={"canonicalName"})
     *
     * @Groups({
     *     "adherent_committees_modal",
     *     "jecoute_region_read",
     *     "cause_read",
     *     "event_list_read",
     *     "event_read",
     *     "committee_sync",
     * })
     */
    protected $slug;

    public function getName(): string
    {
        return $this->name;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
        $this->canonicalName = static::canonicalize($name);
    }

    public static function canonicalize(string $name): string
    {
        return mb_strtolower($name);
    }

    public function updateSlug(string $slug): void
    {
        $this->slug = $slug;
    }

    public function getCanonicalName(): string
    {
        return $this->canonicalName;
    }
}
