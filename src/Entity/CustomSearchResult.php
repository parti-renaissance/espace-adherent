<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\CustomSearchResultRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CustomSearchResultRepository::class)]
#[ORM\Table(name: 'custom_search_results')]
class CustomSearchResult implements EntityMediaInterface
{
    use EntityTimestampableTrait;
    use EntityMediaTrait;

    /**
     * @var int
     */
    #[ORM\Column(type: 'bigint')]
    #[ORM\GeneratedValue]
    #[ORM\Id]
    private $id;

    /**
     * @var string|null
     */
    #[Assert\Length(max: 100)]
    #[Assert\NotBlank]
    #[ORM\Column(length: 100)]
    private $title;

    /**
     * @var string|null
     */
    #[Assert\Sequentially([
        new Assert\NotBlank(),
        new Assert\Length(min: 10, max: 255),
    ])]
    #[ORM\Column]
    private $description;

    /**
     * @var string|null
     */
    #[Assert\Length(max: 255)]
    #[ORM\Column(nullable: true)]
    private $keywords;

    /**
     * @var string|null
     */
    #[ORM\Column]
    private $url;

    public function __toString()
    {
        return $this->title ?: '';
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function setTitle($title)
    {
        $this->title = $title;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function setDescription($description)
    {
        $this->description = $description;
    }

    public function getKeywords()
    {
        return $this->keywords;
    }

    public function setKeywords($keywords)
    {
        $this->keywords = $keywords;
    }

    public function getUrl()
    {
        return $this->url;
    }

    public function setUrl($url)
    {
        $this->url = $url;
    }
}
