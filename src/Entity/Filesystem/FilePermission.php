<?php

namespace App\Entity\Filesystem;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Table(name: 'filesystem_file_permission')]
#[ORM\UniqueConstraint(name: 'file_permission_unique', columns: ['file_id', 'name'])]
#[ORM\Entity]
class FilePermission
{
    /**
     * @var int|null
     */
    #[ORM\Id]
    #[ORM\Column(type: 'integer', options: ['unsigned' => true])]
    #[ORM\GeneratedValue]
    private $id;

    /**
     * @var File
     */
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: File::class, inversedBy: 'permissions')]
    private $file;

    /**
     * @var string
     *
     * @Assert\NotBlank
     * @Assert\Choice(callback={"App\Entity\Filesystem\FilePermissionEnum", "toArray"})
     */
    #[ORM\Column(length: 50)]
    private $name;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFile(): ?File
    {
        return $this->file;
    }

    public function setFile(?File $file = null): void
    {
        $this->file = $file;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function __toString(): string
    {
        return (string) $this->name;
    }
}
