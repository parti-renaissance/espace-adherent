<?php

declare(strict_types=1);

namespace App\Normalizer;

use App\AdherentMessage\AdherentMessageScopeInitializer;
use App\Api\Serializer\PrivatePublicContextBuilder;
use App\Entity\AdherentMessage\AdherentMessage;
use App\Entity\AdherentMessage\AdherentMessageInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class AdherentMessageDenormalizer implements DenormalizerInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;

    public function __construct(private readonly AdherentMessageScopeInitializer $scopeInitializer)
    {
    }

    public function denormalize($data, $type, $format = null, array $context = []): mixed
    {
        if (!isset($context[AbstractNormalizer::OBJECT_TO_POPULATE])) {
            $context[AbstractNormalizer::OBJECT_TO_POPULATE] = new AdherentMessage();
        }

        /** @var AdherentMessageInterface $message */
        $message = $this->denormalizer->denormalize($data, AdherentMessage::class, $format, $context + [__CLASS__ => true]);

        if (!$message->getLabel()) {
            $message->setLabel($message->getSubject());
        }

        $message->setSource(PrivatePublicContextBuilder::CONTEXT_PRIVATE === $context[PrivatePublicContextBuilder::CONTEXT_KEY] ? AdherentMessageInterface::SOURCE_CADRE : AdherentMessageInterface::SOURCE_VOX);

        $this->scopeInitializer->initializeFromScope($message);

        return $message;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [AdherentMessage::class => false];
    }

    public function supportsDenormalization($data, $type, $format = null, array $context = []): bool
    {
        return !isset($context[__CLASS__]) && AdherentMessage::class === $type;
    }
}
