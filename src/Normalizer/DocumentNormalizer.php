<?php

namespace App\Normalizer;

use App\Entity\Document;
use App\Scope\Generator\ScopeGeneratorInterface;
use App\Scope\Scope;
use App\Scope\ScopeGeneratorResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class DocumentNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    private const ALREADY_CALLED = 'DOCUMENT_NORMALIZER_ALREADY_CALLED';

    private ?ScopeGeneratorInterface $currentScope = null;

    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly ScopeGeneratorResolver $scopeGeneratorResolver
    ) {
    }

    /**
     * @param Document $object
     */
    public function normalize($object, $format = null, array $context = [])
    {
        $context[self::ALREADY_CALLED] = true;

        $data = $this->normalizer->normalize($object, $format, $context);

        if (\in_array('document_read', $context['groups'] ?? [])) {
            $data['file_path'] = $object->hasFilePath() ? $this->getUrl($object) : null;
        }

        return $data;
    }

    public function supportsNormalization($data, $format = null, array $context = [])
    {
        return !isset($context[self::ALREADY_CALLED]) && $data instanceof Document;
    }

    private function getUrl(Document $document): string
    {
        $parameters = [
            'uuid' => $document->getUuid()->toString(),
        ];

        if ($scope = $this->getCurrentScope()) {
            $parameters['scope'] = $scope->getCode();
        }

        return $this->urlGenerator->generate('api_documents_get_file_item', $parameters, UrlGeneratorInterface::ABSOLUTE_URL);
    }

    private function getCurrentScope(): ?ScopeGeneratorInterface
    {
        return $this->scopeGeneratorResolver->resolve();
    }
}
