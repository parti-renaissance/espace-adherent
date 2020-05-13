<?php

namespace App\Entity;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(
 *     name="turnkey_projects_files",
 *     uniqueConstraints={@ORM\UniqueConstraint(name="turnkey_projects_file_slug_extension", columns={"slug", "extension"})}
 * )
 *
 * @Algolia\Index(autoIndex=false)
 */
class TurnkeyProjectFile extends BaseFile
{
    /**
     * @var UploadedFile|null
     *
     * @Assert\File(
     *     maxSize="5M",
     *     mimeTypes={
     *         "application/pdf",
     *         "application/x-pdf",
     *         "application/vnd.ms-powerpoint",
     *         "application/vnd.openxmlformats-officedocument.presentationml.presentation",
     *         "application/msword",
     *         "application/vnd.openxmlformats-officedocument.wordprocessingml.document"
     *     },
     *     mimeTypesMessage="turnkey_project_file.valid_mimetypes"
     * )
     */
    protected $file;

    public function getPrefixPath(): string
    {
        return 'files/turnkey_projects_files';
    }
}
