<?php

namespace App\Normalizer;

use App\Entity\Event\Event;
use App\Entity\Jecoute\Riposte;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class UrlDenormalizer implements DenormalizerInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;

    public function denormalize($data, $type, $format = null, array $context = []): mixed
    {
        $urlProperty = 'url';
        if (is_a($type, Event::class, true)) {
            $urlProperty = 'visio_url';
        } elseif (is_a($type, Riposte::class, true)) {
            $urlProperty = 'source_url';
        }

        $url = $data[$urlProperty] ?? '';

        if ('' !== $url && !preg_match('~^[\w+.-]+://~', $url)) {
            $data[$urlProperty] = 'https://'.$url;
        }

        return $this->denormalizer->denormalize($data, $type, $format, $context + [__CLASS__ => true]);
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            '*' => null,
            Event::class => false,
            Riposte::class => false,
        ];
    }

    public function supportsDenormalization($data, $type, $format = null, array $context = []): bool
    {
        return !isset($context[__CLASS__])
            && (
                is_a($type, Event::class, true)
                || Riposte::class === $type
            );
    }
}
