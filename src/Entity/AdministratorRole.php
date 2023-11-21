<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\AdministratorRoleRepository")
 */
#[UniqueEntity(fields: ['code'], message: 'administrator_role.unique_entity.name')]
#[UniqueEntity(fields: ['groupCode', 'label'], message: 'administrator_role.unique_entity.group_label')]
class AdministratorRole
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer", options={"unsigned": true})
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    public ?int $id = null;

    /**
     * @ORM\Column(unique=true)
     */
    #[Assert\NotBlank]
    #[Assert\Length(max: '256')]
    public ?string $code = null;

    /**
     * @ORM\Column
     */
    #[Assert\NotBlank]
    #[Assert\Length(max: '256')]
    public ?string $label = null;

    /**
     * @ORM\Column(type="boolean", options={"default": false})
     */
    public bool $enabled = false;

    /**
     * @ORM\Column(enumType=AdministratorRoleGroupEnum::class)
     */
    #[Assert\NotBlank]
    #[Assert\Type(type: AdministratorRoleGroupEnum::class)]
    public ?AdministratorRoleGroupEnum $groupCode = null;

    /**
     * @ORM\Column
     */
    #[Assert\NotBlank]
    public ?string $description = null;

    public function __toString(): string
    {
        return $this->code;
    }
}
