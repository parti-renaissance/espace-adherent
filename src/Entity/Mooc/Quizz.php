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
class Quizz extends BaseMoocElement
{
    /**
     * @var string
     *
     * @ORM\Column(type="text")
     *
     * @Assert\NotBlank
     */
    private $typeForm;

    public function __construct(string $title = null, string $content = null, string $typeForm = null)
    {
        parent::__construct($title, $content);
        $this->typeForm = $typeForm;
    }

    public function getTypeForm(): ?string
    {
        return $this->typeForm;
    }

    public function setTypeForm(string $typeForm): void
    {
        $this->typeForm = $typeForm;
    }

    public function getType(): string
    {
        return parent::ELEMENT_TYPE_QUIZZ;
    }
}
