<?php

declare(strict_types=1);

namespace App\Normalizer;

use App\Entity\AdherentMessage\AdherentMessageFilter;
use App\JMEFilter\SupportedFilterCodesProvider;
use App\OAuth\Model\Scope as OAuthScope;
use App\Scope\ScopeGeneratorResolver;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

/**
 * Decorates AdherentMessageFilter denormalization by sanitizing input data:
 * only keys matching supported filter codes are kept before deserialization.
 *
 * This runs before AdherentMessageFilterDenormalizer via the serializer chain.
 */
class AdherentMessageFilterSanitizeDenormalizer implements DenormalizerInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;

    public function __construct(
        private readonly ScopeGeneratorResolver $scopeGeneratorResolver,
        private readonly Security $security,
        private readonly SupportedFilterCodesProvider $codesProvider,
    ) {
    }

    public function denormalize($data, $type, $format = null, array $context = []): mixed
    {
        if (\is_array($data) && ($scope = $this->scopeGeneratorResolver->generate())) {
            $feature = $context[AdherentMessageFilterDenormalizer::CONTEXT_FEATURE];
            $isVox = $this->security->isGranted(OAuthScope::generateRole(OAuthScope::JEMARCHE_APP));
            $allowedKeys = $this->codesProvider->getCodes($scope->getMainCode(), $feature, $isVox);

            $data = array_intersect_key($data, array_flip($allowedKeys));
        }

        return $this->denormalizer->denormalize($data, $type, $format, $context + [self::class => true]);
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            AdherentMessageFilter::class => false,
        ];
    }

    public function supportsDenormalization($data, $type, $format = null, array $context = []): bool
    {
        return !isset($context[self::class])
            && AdherentMessageFilter::class === $type
            && isset($context[AdherentMessageFilterDenormalizer::CONTEXT_FEATURE]);
    }
}
