<?php

declare(strict_types=1);

namespace App\Entity\Mooc;

use App\Entity\BaseFile;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'mooc_attachment_file')]
#[ORM\UniqueConstraint(name: 'mooc_attachment_file_slug_extension', columns: ['slug', 'extension'])]
class AttachmentFile extends BaseFile
{
    public function getPrefixPath(): string
    {
        return 'files/mooc';
    }
}
