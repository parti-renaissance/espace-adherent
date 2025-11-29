<?php

declare(strict_types=1);

namespace App\Entity\Formation;

use App\Entity\BaseFile;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\Table(name: 'formation_files')]
#[ORM\UniqueConstraint(name: 'formation_file_slug_extension', columns: ['slug', 'extension'])]
class File extends BaseFile
{
    public const PREFIX_PATH = 'files/formations';

    /**
     * @var UploadedFile|null
     */
    #[Assert\File(maxSize: '5M', binaryFormat: false, mimeTypes: ['image/*', 'video/mpeg', 'video/mp4', 'video/quicktime', 'video/webm', 'application/pdf', 'application/x-pdf', 'application/vnd.ms-powerpoint', 'application/vnd.openxmlformats-officedocument.presentationml.presentation', 'application/msword', 'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/rtf', 'text/plain', 'text/csv', 'text/html', 'text/calendar'])]
    protected $file;

    /**
     * @var Module
     */
    #[ORM\ManyToOne(targetEntity: Module::class, inversedBy: 'files')]
    #[ORM\OrderBy(['id' => 'ASC'])]
    private $module;

    public function getPrefixPath(): string
    {
        return self::PREFIX_PATH;
    }

    public function getModule(): Module
    {
        return $this->module;
    }

    public function setModule(Module $module): void
    {
        $this->module = $module;
    }
}
