<?php

namespace App\Normalizer;

use App\Entity\Adherent;
use App\Entity\AuthorInterface;
use App\Scope\ScopeGeneratorResolver;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

final class AuthorDenormalizer implements DenormalizerInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;

    private const ALREADY_CALLED = 'AUTHOR_DENORMALIZER_ALREADY_CALLED';

    private Security $security;
    private ScopeGeneratorResolver $scopeGeneratorResolver;

    public function __construct(Security $security, ScopeGeneratorResolver $scopeGeneratorResolver)
    {
        $this->security = $security;
        $this->scopeGeneratorResolver = $scopeGeneratorResolver;
    }

    public function denormalize($data, $class, $format = null, array $context = [])
    {
        $context[self::ALREADY_CALLED] = true;

        $data = $this->denormalizer->denormalize($data, $class, $format, $context);

        if (!$data->getId()) {
            $scope = $this->scopeGeneratorResolver->generate();
            $data->setAuthor($scope && ($delegatedAccess = $scope->getDelegatedAccess())
                ? $delegatedAccess->getDelegator()
                : $this->security->getUser()
            );
        }

        return $data;
    }

    public function supportsDenormalization($data, $type, $format = null, array $context = [])
    {
        // Make sure we're not called twice
        if (isset($context[self::ALREADY_CALLED])) {
            return false;
        }

        return is_a($type, AuthorInterface::class, true) && $this->security->getUser() instanceof Adherent;
    }
}
