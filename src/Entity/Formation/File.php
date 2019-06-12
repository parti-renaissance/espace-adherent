<?php

namespace AppBundle\Entity\Formation;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use AppBundle\Entity\BaseFile;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(
 *     name="formation_files",
 *     uniqueConstraints={@ORM\UniqueConstraint(name="formation_file_slug_extension", columns={"slug", "extension"})}
 * )
 *
 * @Algolia\Index(autoIndex=false)
 */
class File extends BaseFile
{
    const PREFIX_PATH = 'files/formations';

    /**
     * @var UploadedFile|null
     *
     * @Assert\File(
     *     maxSize="5M",
     *     mimeTypes={"application/pdf", "application/x-pdf"}
     * )
     */
    protected $file;

    /**
     * @var Article
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Formation\Article", inversedBy="files")
     * @ORM\OrderBy({"id": "ASC"})
     */
    private $article;

    public function getPrefixPath(): string
    {
        return self::PREFIX_PATH;
    }

    public function getArticle(): Article
    {
        return $this->article;
    }

    public function setArticle(Article $article): void
    {
        $this->article = $article;
    }
}
