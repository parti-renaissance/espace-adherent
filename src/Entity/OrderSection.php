<?php

namespace App\Entity;

use App\Repository\OrderSectionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: OrderSectionRepository::class)]
#[ORM\Table(name: 'order_sections')]
class OrderSection
{
    /**
     * @var int
     */
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue]
    #[ORM\Id]
    private $id;

    /**
     * @var string
     */
    #[ORM\Column(length: 50)]
    private $name;

    /**
     * @var int
     */
    #[Assert\NotBlank]
    #[ORM\Column(type: 'smallint')]
    private $position;

    /**
     * @var OrderArticle[]|Collection
     */
    #[ORM\ManyToMany(targetEntity: OrderArticle::class, mappedBy: 'sections')]
    #[ORM\OrderBy(['position' => 'ASC'])]
    private $articles;

    public function __construct(int $position = 1, string $name = '')
    {
        $this->position = $position;
        $this->name = $name;
        $this->articles = new ArrayCollection();
    }

    public function __toString()
    {
        return $this->name ?: '';
    }

    public function getId(): int
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

    public function getPosition(): int
    {
        return $this->position;
    }

    public function setPosition($position): void
    {
        $this->position = $position;
    }

    /**
     * @return OrderArticle[]|Collection
     */
    public function getArticles(): iterable
    {
        return $this->articles;
    }

    public function getPublishedArticlesOrderedByPosition(): iterable
    {
        if ($this->articles->count() > 0) {
            return $this->articles->filter(function (OrderArticle $article) {
                return $article->isPublished();
            });
        } else {
            return $this->articles;
        }
    }
}
