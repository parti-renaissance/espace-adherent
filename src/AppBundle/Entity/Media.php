<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Ramsey\Uuid\Uuid;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * @ORM\Table(name="medias")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\MediaRepository")
 *
 * @UniqueEntity(fields={"path"})
 */
class Media
{
    /**
     * @var UuidInterface
     *
     * @ORM\Column(type="uuid")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
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
     * @Assert\NotBlank
     * @Assert\Image
     */
    private $file;

    public function __construct()
    {
        $this->id = Uuid::uuid4();
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
    }

    public function __toString()
    {
        return $this->getName();
    }

    public function getId(): UuidInterface
    {
        return $this->id;
    }

    public function setId(UuidInterface $id): Media
    {
        $this->id = $id;

        return $this;
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
     * @param UploadedFile|null $file
     * @return Media
     */
    public function setFile(UploadedFile $file = null): Media
    {
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
