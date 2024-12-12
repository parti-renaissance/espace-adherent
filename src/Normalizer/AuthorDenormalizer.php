<?php

namespace App\Normalizer;

use App\Entity\Adherent;
use App\Entity\AuthorInstanceInterface;
use App\Entity\AuthorInterface;
use App\Scope\ScopeGeneratorResolver;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

final class AuthorDenormalizer implements DenormalizerInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;

    public function __construct(
        private readonly Security $security,
        private readonly ScopeGeneratorResolver $scopeGeneratorResolver,
    ) {
    }

    public function denormalize($data, $class, $format = null, array $context = []): mixed
    {
        /** @var AuthorInterface $data */
        $data = $this->denormalizer->denormalize($data, $class, $format, $context + [__CLASS__ => true]);

        if (!$data->getId()) {
            $scope = $this->scopeGeneratorResolver->generate();
            $currentUser = $this->security->getUser();
            $data->setAuthor($scope ? $scope->getMainUser() : $currentUser);

            if ($scope && $data instanceof AuthorInstanceInterface) {
                $data->setAuthor($currentUser);
                $data->setAuthorScope($scope->getCode());
                $data->setAuthorRole($scope->getRoleName());
                $data->setAuthorInstance($scope->getScopeInstance());
                $data->setAuthorZone(implode(', ', $scope->getZoneNames()));
            }
        }

        return $data;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            '*' => null,
            AuthorInterface::class => false,
        ];
    }

    public function supportsDenormalization($data, $type, $format = null, array $context = []): bool
    {
        return !isset($context[__CLASS__])
            && is_a($type, AuthorInterface::class, true)
            && $this->security->getUser() instanceof Adherent;
    }
}
