<?php

namespace App\Normalizer;

use App\Entity\Adherent;
use App\Entity\AuthorInstanceInterface;
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

    public function __construct(
        private readonly Security $security,
        private readonly ScopeGeneratorResolver $scopeGeneratorResolver,
    ) {
    }

    public function denormalize($data, $class, $format = null, array $context = [])
    {
        $context[self::ALREADY_CALLED] = true;

        /** @var AuthorInterface $data */
        $data = $this->denormalizer->denormalize($data, $class, $format, $context);

        if (!$data->getId()) {
            $scope = $this->scopeGeneratorResolver->generate();
            $data->setAuthor($scope && $scope->getDelegatedAccess() ? $scope->getDelegator() : $this->security->getUser());

            if ($scope && $data instanceof AuthorInstanceInterface) {
                $data->setAuthorRole($scope->getRoleName());
                $data->setAuthorInstance($scope->getScopeInstance());
                $data->setAuthorZone(implode(', ', $scope->getZoneNames()));
            }
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
