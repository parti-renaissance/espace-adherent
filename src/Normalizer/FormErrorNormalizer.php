<?php

namespace App\Normalizer;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class FormErrorNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    public const GROUP = 'form_errors';

    /** @param FormInterface  $object */
    public function normalize($object, $format = null, array $context = [])
    {
        $form = [];
        $errors = [];

        foreach ($object->getErrors() as $error) {
            $errors[] = $error->getMessage();
        }

        if ($errors) {
            $form['errors'] = $errors;
        }

        $children = [];
        foreach ($object->all() as $child) {
            if ($child instanceof FormInterface) {
                $children[$child->getName()] = $this->normalizer->normalize($child, $format, $context);
            }
        }

        if ($children) {
            $form['children'] = $children;
        }

        return $form;
    }

    public function supportsNormalization($data, $format = null, array $context = [])
    {
        return $data instanceof FormInterface
            && \in_array(self::GROUP, $context['groups'] ?? []);
    }
}
