<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\AdministratorRoleRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: AdministratorRoleRepository::class)]
#[UniqueEntity(fields: ['code'], message: 'administrator_role.unique_entity.name')]
#[UniqueEntity(fields: ['groupCode', 'label'], message: 'administrator_role.unique_entity.group_label')]
class AdministratorRole implements \Stringable
{
    #[ORM\Column(type: 'integer', options: ['unsigned' => true])]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Id]
    public ?int $id = null;

    #[Assert\Length(max: 256)]
    #[Assert\NotBlank]
    #[ORM\Column(unique: true)]
    public ?string $code = null;

    #[Assert\Length(max: 256)]
    #[Assert\NotBlank]
    #[ORM\Column]
    public ?string $label = null;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    public bool $enabled = false;

    #[Assert\NotBlank]
    #[Assert\Type(type: AdministratorRoleGroupEnum::class)]
    #[ORM\Column(enumType: AdministratorRoleGroupEnum::class)]
    public ?AdministratorRoleGroupEnum $groupCode = null;

    #[Assert\NotBlank]
    #[ORM\Column]
    public ?string $description = null;

    public function __toString(): string
    {
        return $this->code;
    }
}
