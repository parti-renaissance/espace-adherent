<?php

namespace App\Normalizer;

use App\Entity\Document;
use Symfony\Component\Mime\MimeTypesInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class DocumentNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly MimeTypesInterface $mimeTypes,
    ) {
    }

    /**
     * @param Document $object
     */
    public function normalize($object, $format = null, array $context = []): array|string|int|float|bool|\ArrayObject|null
    {
        $data = $this->normalizer->normalize($object, $format, $context + [__CLASS__ => true]);

        if (\in_array('document_read', $context['groups'] ?? [])) {
            $data['file_url'] = $data['file_type'] = null;

            if ($object->hasFilePath()) {
                $data['file_url'] = $this->getUrl($object);
                $data['file_type'] = $this->mimeTypes->getMimeTypes(pathinfo($object->filePath, \PATHINFO_EXTENSION))[0] ?? null;
            }
        }

        return $data;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            '*' => null,
            Document::class => false,
        ];
    }

    public function supportsNormalization($data, $format = null, array $context = []): bool
    {
        return !isset($context[__CLASS__]) && $data instanceof Document;
    }

    private function getUrl(Document $document): string
    {
        return $this->urlGenerator->generate(
            '_api_/v3/documents/{uuid}/file_get',
            ['uuid' => $document->getUuid()],
            UrlGeneratorInterface::ABSOLUTE_URL
        );
    }
}
