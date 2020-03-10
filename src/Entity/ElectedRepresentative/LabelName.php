<?php

namespace AppBundle\Entity\ElectedRepresentative;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ElectedRepresentative\LabelNameRepository")
 * @ORM\Table(
 *     name="elected_representative_label_name",
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(name="elected_representative_label_name_name_unique", columns="name"),
 *     }
 * )
 *
 * @UniqueEntity("name")
 *
 * @Algolia\Index(autoIndex=false)
 */
class LabelName
{
    public const LABEL_LAREM = 'LaREM';

    /**
     * @var int|null
     *
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    private $id;

    /**
     * @var string|null
     *
     * @ORM\Column(length=255)
     *
     * @Assert\NotBlank
     * @Assert\Length(max="255")
     */
    private $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }
}
