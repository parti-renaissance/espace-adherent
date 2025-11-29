<?php

declare(strict_types=1);

namespace App\Form\Admin\Filesystem;

use App\Entity\Filesystem\File;
use App\Repository\Filesystem\FileRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class FileParentType extends AbstractType implements DataTransformerInterface
{
    private $repository;

    public function __construct(FileRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getParent(): string
    {
        return TextType::class;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addModelTransformer($this);
    }

    public function transform($value): mixed
    {
        if ($value instanceof File) {
            return $value->getName();
        }

        return null;
    }

    public function reverseTransform($value): mixed
    {
        if (!empty($value)) {
            if ($file = $this->repository->findDirectoryByName($value)) {
                return $file;
            }

            $file = new File();
            $file->setName($value);
            $file->markAsDir();

            return $file;
        }

        return null;
    }
}
