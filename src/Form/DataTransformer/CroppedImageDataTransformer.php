<?php

namespace App\Form\DataTransformer;

use App\Entity\ImageOwnerInterface;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class CroppedImageDataTransformer implements DataTransformerInterface
{
    public function transform($value)
    {
        if ($value instanceof ImageOwnerInterface) {
            return $value->getImage();
        }

        return $value;
    }

    public function reverseTransform($value)
    {
        if (!empty($value)) {
            if (!$value['image'] instanceof UploadedFile) {
                if (null === $value['image']) {
                    return null;
                }

                throw new TransformationFailedException('Fichier incorrect');
            }

            return $value['image'];
        }

        return $value;
    }
}
