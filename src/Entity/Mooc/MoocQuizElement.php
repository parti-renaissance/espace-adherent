<?php

namespace AppBundle\Entity\Mooc;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 *
 * @Algolia\Index(autoIndex=false)
 */
class MoocQuizElement extends BaseMoocElement
{
    /**
     * @var string
     *
     * @ORM\Column
     *
     * @Assert\NotBlank
     * @Assert\Url
     */
    private $typeformUrl;

    public function __construct(
        string $title = null,
        string $content = null,
        string $shareTwitterText = null,
        string $shareFacebokText = null,
        string $shareEmailObject = null,
        string $shareEmailBody = null,
        string $typeformUrl = null
    ) {
        parent::__construct($title, $content, $shareTwitterText, $shareFacebokText, $shareEmailObject, $shareEmailBody);
        $this->typeformUrl = $typeformUrl;
    }

    public function getTypeformUrl(): ?string
    {
        return $this->typeformUrl;
    }

    public function setTypeformUrl(string $typeformUrl): void
    {
        $this->typeformUrl = $typeformUrl;
    }

    public function getType(): string
    {
        return MoocElementTypeEnum::QUIZ;
    }
}
