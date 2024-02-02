<?php

namespace App\Entity\JeMengage;

use App\Entity\EntityTimestampableTrait;
use App\Entity\ExposedImageOwnerInterface;
use App\Entity\ImageTrait;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="jemengage_header_blocks")
 * @ORM\Entity(repositoryClass="App\Repository\JeMengage\HeaderBlockRepository")
 *
 * @UniqueEntity("name", message="header_block.name.unique")
 */
class HeaderBlock implements ExposedImageOwnerInterface
{
    use EntityTimestampableTrait;
    use ImageTrait;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer", options={"unsigned": true})
     * @ORM\GeneratedValue
     */
    private ?int $id = null;

    /**
     * @ORM\Column(length=100, unique=true)
     *
     * @Assert\NotBlank(message="header_block.name.not_blank")
     * @Assert\Length(max="100", maxMessage="header_block.name.max_length")
     *
     * @Groups({"header_block_read"})
     */
    private ?string $name = null;

    /**
     * @ORM\Column(length=130, unique=true)
     * @Gedmo\Slug(fields={"name"})
     */
    private ?string $slug = null;

    /**
     * @ORM\Column(length=50)
     *
     * @Assert\NotBlank(message="header_block.prefix.not_blank")
     * @Assert\Length(max="50", maxMessage="header_block.prefix.max_length")
     *
     * @Groups({"header_block_read"})
     */
    private ?string $prefix = null;

    /**
     * @ORM\Column(length=100, nullable=true)
     *
     * @Assert\Length(max="100", maxMessage="header_block.slogan.max_length")
     *
     * @Groups({"header_block_read"})
     */
    private ?string $slogan = null;

    /**
     * @ORM\Column(type="text", nullable=true)
     *
     * @Groups({"header_block_read"})
     */
    private ?string $content = null;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     *
     * Assert\@Assert\GreaterThan("now")
     */
    private ?\DateTime $deadlineDate = null;

    public function __construct(
        ?string $name = null,
        ?string $prefix = null,
        ?string $slogan = null,
        ?string $content = null,
        ?\DateTime $deadlineDate = null
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
        return $this->imageName ? sprintf('images/jemengage/header_block/%s', $this->getImageName()) : '';
    }
}
