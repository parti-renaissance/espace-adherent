<?php

namespace AppBundle\Entity\Mooc;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use AppBundle\Entity\BaseFile;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(
 *     name="mooc_attachment_file",
 *     uniqueConstraints={@ORM\UniqueConstraint(name="mooc_attachment_file_slug_extension", columns={"slug", "extension"})}
 * )
 *
 * @Algolia\Index(autoIndex=false)
 */
class AttachmentFile extends BaseFile
{
    public function getPrefixPath(): string
    {
        return 'files/mooc';
    }
}
