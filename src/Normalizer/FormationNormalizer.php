<?php

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

    private const ALREADY_CALLED = 'ADHERENT_FORMATION_NORMALIZER_ALREADY_CALLED';

    private ?ScopeGeneratorInterface $currentScope = null;

    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly ScopeGeneratorResolver $scopeGeneratorResolver
    ) {
    }

    /**
     * @param Formation $object
     */
    public function normalize($object, $format = null, array $context = [])
    {
        $context[self::ALREADY_CALLED] = true;

        $data = $this->normalizer->normalize($object, $format, $context);

        if (array_intersect(['formation_read', 'formation_list_read'], $context['groups'] ?? [])) {
            $data['file_path'] = $object->isFileContent() && $object->hasFilePath() ? $this->getUrl($object) : null;
        }

        return $data;
    }

    public function supportsNormalization($data, $format = null, array $context = [])
    {
        return !isset($context[self::ALREADY_CALLED]) && $data instanceof Formation;
    }

    private function getUrl(Formation $formation): string
    {
        $parameters = [
            'uuid' => $formation->getUuid()->toString(),
        ];

        if ($scope = $this->getCurrentScope()) {
            $parameters['scope'] = $scope->getCode();
        }

        return $this->urlGenerator->generate('_api_/formations/{uuid}/file_get', $parameters, UrlGeneratorInterface::ABSOLUTE_URL);
    }

    private function getCurrentScope(): ?ScopeGeneratorInterface
    {
        if (!$this->currentScope) {
            $this->currentScope = $this->scopeGeneratorResolver->resolve();
        }

        return $this->currentScope;
    }
}
