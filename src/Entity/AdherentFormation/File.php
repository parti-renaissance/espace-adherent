<?php

namespace App\Entity\AdherentFormation;

use App\Entity\BaseFile;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Serializer\Annotation as SymfonySerializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(
 *     name="adherent_formation_file",
 *     uniqueConstraints={@ORM\UniqueConstraint(name="adherent_formation_file_slug_extension", columns={"slug", "extension"})}
 * )
 */
class File extends BaseFile
{
    public const PREFIX_PATH = 'files/adherent_formations';

    /**
     * @var UploadedFile|null
     *
     * @Assert\File(
     *     maxSize="5M",
     *     binaryFormat=false,
     *     mimeTypes={
     *         "image/*",
     *         "video/mpeg",
     *         "video/mp4",
     *         "video/quicktime",
     *         "video/webm",
     *         "application/pdf",
     *         "application/x-pdf",
     *         "application/vnd.ms-powerpoint",
     *         "application/vnd.openxmlformats-officedocument.presentationml.presentation",
     *         "application/msword",
     *         "application/vnd.ms-excel",
     *         "application/vnd.openxmlformats-officedocument.wordprocessingml.document",
     *         "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
     *         "application/rtf",
     *         "text/plain",
     *         "text/csv",
     *         "text/html",
     *         "text/calendar"
     *     }
     * )
     *
     * @SymfonySerializer\Groups({
     *     "formation_write",
     * })
     */
    protected $file;

    public function getPrefixPath(): string
    {
        return self::PREFIX_PATH;
    }
}
