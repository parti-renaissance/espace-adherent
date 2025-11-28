<?php

declare(strict_types=1);

namespace App\Entity\JeMengage;

use App\Entity\EntityTimestampableTrait;
use App\Entity\ImageExposeInterface;
use App\Entity\ImageManageableInterface;
use App\Entity\ImageTrait;
use App\Repository\JeMengage\HeaderBlockRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: HeaderBlockRepository::class)]
#[ORM\Table(name: 'jemengage_header_blocks')]
#[UniqueEntity(fields: ['name'], message: 'header_block.name.unique')]
class HeaderBlock implements ImageManageableInterface, ImageExposeInterface
{
    use EntityTimestampableTrait;
    use ImageTrait;

    #[ORM\Column(type: 'integer', options: ['unsigned' => true])]
    #[ORM\GeneratedValue]
    #[ORM\Id]
    private ?int $id = null;

    #[Assert\Length(max: 100, maxMessage: 'header_block.name.max_length')]
    #[Assert\NotBlank(message: 'header_block.name.not_blank')]
    #[Groups(['header_block_read'])]
    #[ORM\Column(length: 100, unique: true)]
    private ?string $name = null;

    #[Gedmo\Slug(fields: ['name'])]
    #[ORM\Column(length: 130, unique: true)]
    private ?string $slug = null;

    #[Assert\Length(max: 50, maxMessage: 'header_block.prefix.max_length')]
    #[Assert\NotBlank(message: 'header_block.prefix.not_blank')]
    #[Groups(['header_block_read'])]
    #[ORM\Column(length: 50)]
    private ?string $prefix = null;

    #[Assert\Length(max: 100, maxMessage: 'header_block.slogan.max_length')]
    #[Groups(['header_block_read'])]
    #[ORM\Column(length: 100, nullable: true)]
    private ?string $slogan = null;

    #[Groups(['header_block_read'])]
    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $content = null;

    #[Assert\GreaterThan('now')]
    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTime $deadlineDate = null;

    public function __construct(
        ?string $name = null,
        ?string $prefix = null,
        ?string $slogan = null,
        ?string $content = null,
        ?\DateTime $deadlineDate = null,
    ) {
        $this->name = $name;
        $this->prefix = $prefix;
        $this->slogan = $slogan;
        $this->content = $content;
        $this->deadlineDate = $deadlineDate;
    }

    public function __toString(): string
    {
        return (string) $this->name;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(?string $slug): void
    {
        $this->slug = $slug;
    }

    public function getPrefix(): ?string
    {
        return $this->prefix;
    }

    public function setPrefix(?string $prefix): void
    {
        $this->prefix = $prefix;
    }

    public function getSlogan(): ?string
    {
        return $this->slogan;
    }

    public function setSlogan(?string $slogan): void
    {
        $this->slogan = $slogan;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(?string $content): void
    {
        $this->content = $content;
    }

    public function getDeadlineDate(): ?\DateTime
    {
        return $this->deadlineDate;
    }

    public function setDeadlineDate(?\DateTime $deadlineDate): void
    {
        $this->deadlineDate = $deadlineDate;
    }

    public function getImagePath(): string
    {
        return $this->imageName ? \sprintf('images/jemengage/header_block/%s', $this->getImageName()) : '';
    }
}
