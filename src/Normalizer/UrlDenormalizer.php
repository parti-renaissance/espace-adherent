<?php

namespace App\Normalizer;

use App\Entity\Coalition\QuickAction;
use App\Entity\Event\BaseEvent;
use App\Entity\Jecoute\Riposte;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class UrlDenormalizer implements DenormalizerInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;

    private const ALREADY_CALLED = 'URL_DENORMALIZER_ALREADY_CALLED';

    public function denormalize($data, $type, $format = null, array $context = [])
    {
        $context[self::ALREADY_CALLED] = true;

        $urlProperty = 'url';
        if (is_a($type, BaseEvent::class, true)) {
            $urlProperty = 'visio_url';
        } elseif (is_a($type, Riposte::class, true)) {
            $urlProperty = 'source_url';
        }

        $url = $data[$urlProperty] ?? '';

        if ('' !== $url && !preg_match('~^[\w+.-]+://~', $url)) {
            $data[$urlProperty] = 'https://'.$url;
        }

        return $this->denormalizer->denormalize($data, $type, $format, $context);
    }

    public function supportsDenormalization($data, $type, $format = null, array $context = [])
    {
        return !isset($context[self::ALREADY_CALLED])
            && (
                is_a($type, BaseEvent::class, true)
                || QuickAction::class === $type
                || Riposte::class === $type
            );
    }
}
