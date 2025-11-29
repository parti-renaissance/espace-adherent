<?php

declare(strict_types=1);

namespace App\Normalizer;

use App\Entity\AdherentFormation\Formation;
use App\Scope\Generator\ScopeGeneratorInterface;
use App\Scope\ScopeGeneratorResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class FormationNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    private ?ScopeGeneratorInterface $currentScope = null;

    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly ScopeGeneratorResolver $scopeGeneratorResolver,
    ) {
    }

    /**
     * @param Formation $object
     */
    public function normalize($object, $format = null, array $context = []): array|string|int|float|bool|\ArrayObject|null
    {
        $data = $this->normalizer->normalize($object, $format, $context + [__CLASS__ => true]);

        if (array_intersect(['formation_read', 'formation_list_read'], $context['groups'] ?? [])) {
            $data['file_path'] = $object->isFileContent() && $object->hasFilePath() ? $this->getUrl($object) : null;
        }

        return $data;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            Formation::class => false,
        ];
    }

    public function supportsNormalization($data, $format = null, array $context = []): bool
    {
        return !isset($context[__CLASS__]) && $data instanceof Formation;
    }

    private function getUrl(Formation $formation): string
    {
        $parameters = [
            'uuid' => $formation->getUuid()->toString(),
        ];

        if ($scope = $this->getCurrentScope()) {
            $parameters['scope'] = $scope->getCode();
        }

        return $this->urlGenerator->generate('_api_/v3/formations/{uuid}/file_get', $parameters, UrlGeneratorInterface::ABSOLUTE_URL);
    }

    private function getCurrentScope(): ?ScopeGeneratorInterface
    {
        if (!$this->currentScope) {
            $this->currentScope = $this->scopeGeneratorResolver->resolve();
        }

        return $this->currentScope;
    }
}
