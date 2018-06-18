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
class Quiz extends BaseMoocElement
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
        string $twitterText = null,
        string $facebokText = null,
        string $emailObject = null,
        string $emailBody = null,
        string $typeformUrl = null
    ) {
        parent::__construct($title, $content, $twitterText, $facebokText, $emailObject, $emailBody);
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
}
