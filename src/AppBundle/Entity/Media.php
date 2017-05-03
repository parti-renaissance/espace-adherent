<?php

namespace AppBundle\Entity;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * @ORM\Table(name="medias")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\MediaRepository")
 *
 * @UniqueEntity(fields={"path"})
 *
 * @Algolia\Index(autoIndex=false)
 */
class Media
{
    /**
     * @var int
     *
     * @ORM\Column(type="bigint")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string|null
     *
     * @ORM\Column(type="string")
     *
     * @Assert\NotBlank
     */
    private $name;

    /**
     * @var string|null
     *
     * @ORM\Column(length=255, unique=true)
     *
     * @Assert\NotBlank
     */
    private $path;

    /**
     * @var int|null
     *
     * @ORM\Column(type="integer")
     */
    private $width;

    /**
     * @var int|null
     *
     * @ORM\Column(type="integer")
     */
    private $height;

    /**
     * @var int|null
     *
     * @ORM\Column(type="bigint")
     */
    private $size;

    /**
     * @var string|null
     *
     * @ORM\Column(length=50)
     */
    private $mimeType;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     *
     * @Gedmo\Timestampable(on="create")
     */
    private $createdAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     *
     * @Gedmo\Timestampable(on="update")
     */
    private $updatedAt;

    /**
     * @var UploadedFile|null
     *
     * @Assert\Image
     */
    private $file;

    public function __toString()
    {
        return $this->name ?: '';
    }

    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return null|string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param null|string $name
     *
     * @return Media
     */
    public function setName($name): Media
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @param null|string $path
     *
     * @return Media
     */
    public function setPath($path): Media
    {
        $this->path = $path;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * @param int|null $width
     *
     * @return Media
     */
    public function setWidth($width): Media
    {
        $this->width = $width;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getHeight()
    {
        return $this->height;
    }

    /**
     * @param int|null $height
     *
     * @return Media
     */
    public function setHeight($height): Media
    {
        $this->height = $height;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * @param int|null $size
     *
     * @return Media
     */
    public function setSize($size): Media
    {
        $this->size = $size;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getMimeType()
    {
        return $this->mimeType;
    }

    /**
     * @param null|string $mimeType
     *
     * @return Media
     */
    public function setMimeType($mimeType): Media
    {
        $this->mimeType = $mimeType;

        return $this;
    }

    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTime $createdAt): Media
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): \DateTime
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTime $updatedAt): Media
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * @return null|UploadedFile
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * @param File|null $file
     *
     * @return Media
     */
    public function setFile(File $file = null): Media
    {
        if (!$file) {
            return $this;
        }

        $infos = getimagesize($file->getPathname());

        if (!count($infos)) {
            return $this;
        }

        $this->file = $file;
        $this->size = $file->getSize();
        $this->width = $infos[0];
        $this->height = $infos[1];
        $this->mimeType = $infos['mime'];

        return $this;
    }
}
