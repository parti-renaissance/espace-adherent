<?php

namespace AppBundle\Entity;

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
     *     mimeTypes={"application/pdf", "application/x-pdf"},
     *     mimeTypesMessage="turnkey_project_file.valid_mimetypes"
     * )
     */
    protected $file;

    public function getPrefixPath(): string
    {
        return 'files/turnkey_projects_files';
    }
}
