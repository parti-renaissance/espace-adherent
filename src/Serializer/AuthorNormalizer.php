<?php

namespace AppBundle\Serializer;

use AppBundle\Entity\IdeasWorkshop\Idea;
use AppBundle\Entity\IdeasWorkshop\ThreadComment;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class AuthorNormalizer implements DenormalizerInterface
{
    private $normalizer;
    private $tokenStorage;

    public function __construct(NormalizerInterface $normalizer, TokenStorageInterface $tokenStorage)
    {
        if (!$normalizer instanceof DenormalizerInterface) {
            throw new \InvalidArgumentException('The normalizer must implement the DenormalizerInterface');
        }

        $this->normalizer = $normalizer;
        $this->tokenStorage = $tokenStorage;
    }

    public function denormalize($data, $class, $format = null, array $context = [])
    {
        if (!isset($data['id'])) {
            $data['author'] = $this->tokenStorage->getToken()->getUser();
        }

        $data = $this->normalizer->denormalize($data, $class, $format, $context);

        return $data;
    }

    public function supportsDenormalization($data, $type, $format = null)
    {
        return Idea::class === $type || ThreadComment::class === $type;
    }
}
