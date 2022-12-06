<?php

namespace App\Entity\AdherentFormation;

use App\Entity\BaseFile;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\UploadedFile;
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
     *     mimeTypes={"application/pdf", "application/x-pdf"}
     * )
     */
    protected $file;

    public function getPrefixPath(): string
    {
        return self::PREFIX_PATH;
    }
}
