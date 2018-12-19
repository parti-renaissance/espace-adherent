<?php

namespace AppBundle\Normalizer;

use AppBundle\Entity\AuthorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

final class AuthorNormalizer implements DenormalizerInterface
{
    private $normalizer;
    private $tokenStorage;

    public function __construct(DenormalizerInterface $normalizer, TokenStorageInterface $tokenStorage)
    {
        $this->normalizer = $normalizer;
        $this->tokenStorage = $tokenStorage;
    }

    public function denormalize($data, $class, $format = null, array $context = [])
    {
        $data = $this->normalizer->denormalize($data, $class, $format, $context);
        if (!$data->getId()) {
            $data->setAuthor($this->tokenStorage->getToken()->getUser());
        }

        return $data;
    }

    public function supportsDenormalization($data, $type, $format = null)
    {
        return is_a($type, AuthorInterface::class, true)
            && \is_object($this->tokenStorage->getToken()->getUser());
    }
}
