<?php

declare(strict_types=1);

namespace App\Entity\Mooc;

use App\Entity\EntityTimestampableTrait;
use App\Entity\Image;
use App\Repository\MoocRepository;
use App\Validator\ImageObject as AssertImageObject;
use Cake\Chronos\Chronos;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[Assert\Expression(expression: '(this.getArticleImage() and null === this.getYoutubeId()) or (this.getYoutubeId() and null === this.getArticleImage())', message: 'mooc.two_media')]
#[ORM\Entity(repositoryClass: MoocRepository::class)]
#[UniqueEntity(fields: ['title'])]
class Mooc implements \Stringable
{
    use EntityTimestampableTrait;

    #[ORM\Column(type: 'integer', options: ['unsigned' => true])]
    #[ORM\GeneratedValue]
    #[ORM\Id]
    private $id;

    #[Assert\Length(max: 255)]
    #[Assert\NotBlank]
    #[Groups(['mooc_list'])]
    #[ORM\Column]
    private $title;

    /**
     * @var string|null
     */
    #[Groups(['mooc_list'])]
    #[ORM\Column(type: 'text', nullable: true)]
    private $description;

    #[Gedmo\Slug(fields: ['title'], unique: true)]
    #[Groups(['mooc_list'])]
    #[ORM\Column(unique: true)]
    private $slug;

    /**
     * @var Chapter[]|Collection
     */
    #[Assert\Valid]
    #[ORM\OneToMany(mappedBy: 'mooc', targetEntity: Chapter::class, cascade: ['all'])]
    #[ORM\OrderBy(['position' => 'ASC'])]
    private $chapters;

    #[Assert\Length(min: 5, max: 800)]
    #[ORM\Column(length: 800, nullable: true)]
    private $content;

    /**
     * @var string|null
     */
    #[Assert\Length(min: 2, max: 11)]
    #[Assert\Regex(pattern: '/^[A-Za-z0-9_-]+$/', message: 'mooc.youtubeid_syntax')]
    #[ORM\Column(nullable: true)]
    private $youtubeId;

    /**
     * @var \DateTimeInterface|null
     */
    #[Assert\Time]
    #[ORM\Column(type: 'time', nullable: true)]
    private $youtubeDuration;

    #[Assert\Length(max: 255)]
    #[Assert\NotBlank]
    #[ORM\Column]
    private $shareTwitterText;

    #[Assert\Length(max: 255)]
    #[Assert\NotBlank]
    #[ORM\Column]
    private $shareFacebookText;

    #[Assert\Length(max: 255)]
    #[Assert\NotBlank]
    #[ORM\Column]
    private $shareEmailSubject;

    #[Assert\Sequentially([
        new Assert\NotBlank(),
        new Assert\Length(min: 5, max: 500),
    ])]
    #[ORM\Column(length: 500)]
    protected $shareEmailBody;

    /**
     * @var Image|null
     */
    #[AssertImageObject(
        maxSize: '1M',
        mimeTypes: ['image/jpeg', 'image/png'],
        maxWidth: 960,
        maxHeight: 720
    )]
    #[ORM\OneToOne(targetEntity: Image::class, cascade: ['all'], orphanRemoval: true)]
    protected $articleImage;

    /**
     * @var Image|null
     */
    #[AssertImageObject(
        maxSize: '1M',
        mimeTypes: ['image/jpeg', 'image/png'],
        maxWidth: 960,
        maxHeight: 720
    )]
    #[ORM\OneToOne(targetEntity: Image::class, cascade: ['all'], orphanRemoval: true)]
    protected $listImage;

    public function __construct(
        ?string $title = null,
        ?string $description = null,
        ?string $content = null,
        ?string $youtubeId = null,
        ?\DateTimeInterface $youtubeDuration = null,
        ?string $shareTwitterText = null,
        ?string $shareFacebookText = null,
        ?string $shareEmailSubject = null,
        ?string $shareEmailBody = null,
    ) {
        $this->title = $title;
        $this->description = $description;
        $this->chapters = new ArrayCollection();
        $this->content = $content;
        $this->youtubeId = $youtubeId;
        $this->youtubeDuration = $youtubeDuration ?? Chronos::create();
        $this->shareTwitterText = $shareTwitterText;
        $this->shareFacebookText = $shareFacebookText;
        $this->shareEmailSubject = $shareEmailSubject;
        $this->shareEmailBody = $shareEmailBody;
    }

    public function __toString()
    {
        return (string) $this->title;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): void
    {
        $this->slug = $slug;
    }

    /**
     * @return Chapter[]|Collection|iterable
     */
    public function getChapters(): iterable
    {
        return $this->chapters;
    }

    #[Groups(['mooc_list'])]
    public function getChapterCount(): int
    {
        return $this->chapters->count();
    }

    public function addChapter(Chapter $chapter): void
    {
        if (!$this->chapters->contains($chapter)) {
            $chapter->setMooc($this);
            $this->chapters->add($chapter);
        }
    }

    public function removeChapter(Chapter $chapter): void
    {
        $chapter->detachMooc();
        $this->chapters->removeElement($chapter);
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): void
    {
        $this->content = $content;
    }

    public function getYoutubeId(): ?string
    {
        return $this->youtubeId;
    }

    public function setYoutubeId(?string $youtubeId): void
    {
        $this->youtubeId = $youtubeId;
    }

    public function hasYoutubeThumbnail(): bool
    {
        return null !== $this->youtubeId;
    }

    public function getYoutubeThumbnail(): ?string
    {
        return $this->youtubeId ? \sprintf('https://img.youtube.com/vi/%s/0.jpg', $this->youtubeId) : null;
    }

    public function getYoutubeDuration(): ?\DateTime
    {
        return $this->youtubeDuration;
    }

    public function setYoutubeDuration(?\DateTime $youtubeDuration): void
    {
        $this->youtubeDuration = $youtubeDuration;
    }

    public function getShareTwitterText(): ?string
    {
        return $this->shareTwitterText;
    }

    public function setShareTwitterText(string $shareTwitterText): void
    {
        $this->shareTwitterText = $shareTwitterText;
    }

    public function getShareFacebookText(): ?string
    {
        return $this->shareFacebookText;
    }

    public function setShareFacebookText(string $shareFacebookText): void
    {
        $this->shareFacebookText = $shareFacebookText;
    }

    public function getShareEmailSubject(): ?string
    {
        return $this->shareEmailSubject;
    }

    public function setShareEmailSubject(string $shareEmailSubject): void
    {
        $this->shareEmailSubject = $shareEmailSubject;
    }

    public function getShareEmailBody(): ?string
    {
        return $this->shareEmailBody;
    }

    public function setShareEmailBody(string $shareEmailBody): void
    {
        $this->shareEmailBody = $shareEmailBody;
    }

    public function getArticleImage(): ?Image
    {
        return $this->articleImage;
    }

    public function setArticleImage(?Image $articleImage): void
    {
        $this->articleImage = $articleImage;
    }

    public function getListImage(): ?Image
    {
        return $this->listImage;
    }

    public function setListImage(?Image $listImage): void
    {
        $this->listImage = $listImage;
    }
}
