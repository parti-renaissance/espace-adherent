<?php

declare(strict_types=1);

namespace App\Form\Admin;

use App\Entity\UploadableFile;
use Sonata\AdminBundle\Form\Type\AdminType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UploadableFileType extends AbstractType implements DataTransformerInterface
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addModelTransformer($this);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'required' => false,
            'data_class' => UploadableFile::class,
        ]);
    }

    public function getParent(): string
    {
        return AdminType::class;
    }

    public function transform(mixed $value)
    {
        if ($value instanceof UploadableFile && ($value->uploadFile || false === $value->getFile()?->isEmpty())) {
            return $value;
        }

        return null;
    }

    public function reverseTransform(mixed $value)
    {
        return $this->transform($value);
    }
}
