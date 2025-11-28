<?php

declare(strict_types=1);

namespace App\Form\DataTransformer;

use libphonenumber\PhoneNumber;
use Misd\PhoneNumberBundle\Form\DataTransformer\PhoneNumberToArrayTransformer as BasePhoneNumberToArrayTransformer;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class PhoneNumberToArrayTransformer implements DataTransformerInterface
{
    public function __construct(private readonly BasePhoneNumberToArrayTransformer $decorated)
    {
    }

    public function transform($value): array
    {
        try {
            return $this->decorated->transform($value);
        } catch (TransformationFailedException $e) {
            return [
                'country' => null,
                'number' => $value instanceof PhoneNumber ? $value->getNationalNumber() : '',
            ];
        }
    }

    public function reverseTransform($value): mixed
    {
        return $this->decorated->reverseTransform($value);
    }
}
