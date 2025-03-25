<?php

namespace App\Normalizer;

use ApiPlatform\Metadata\HttpOperation;
use App\AdherentMessage\AdherentMessageFactory;
use App\Entity\AdherentMessage\AbstractAdherentMessage;
use App\Entity\AdherentMessage\AdherentMessageInterface;
use Symfony\Component\Serializer\Exception\UnexpectedValueException;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class AdherentMessageDenormalizer implements DenormalizerInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;

    public function denormalize($data, $type, $format = null, array $context = []): mixed
    {
        /** @var AdherentMessageInterface|null $oldMessage */
        $oldMessage = null;

        if (!empty($context[AbstractNormalizer::OBJECT_TO_POPULATE])) {
            $messageClass = \get_class($context[AbstractNormalizer::OBJECT_TO_POPULATE]);
            $oldMessage = clone $context[AbstractNormalizer::OBJECT_TO_POPULATE];
        } else {
            $messageType = $data['type'] ?? null;

            if (!$messageType || !($messageClass = AdherentMessageFactory::getMessageClassName($messageType))) {
                throw new UnexpectedValueException('Type value is missing or invalid');
            }
        }

        if (!$messageClass) {
            throw new UnexpectedValueException('Type value is missing or invalid');
        }

        unset($data['type']);

        if (!isset($context[AbstractNormalizer::OBJECT_TO_POPULATE])) {
            $context[AbstractNormalizer::OBJECT_TO_POPULATE] = new $messageClass();
        }
        $context['resource_class'] = $messageClass;
        /** @var HttpOperation $operation */
        $operation = $context['operation'];
        $context['operation'] = $operation->withClass($messageClass);

        /** @var AdherentMessageInterface $message */
        $message = $this->denormalizer->denormalize($data, $messageClass, $format, $context + [__CLASS__ => true]);

        $message->setSource(AdherentMessageInterface::SOURCE_API);

        if (
            ($context['operation_name'] ?? null) === '_api_/v3/adherent_messages/{uuid}_put'
            && $oldMessage
            && (
                $oldMessage->getContent() !== $message->getContent()
                || $oldMessage->getSubject() !== $message->getSubject()
            )
        ) {
            $message->setSynchronized(false);
        }

        return $message;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            AbstractAdherentMessage::class => false,
        ];
    }

    public function supportsDenormalization($data, $type, $format = null, array $context = []): bool
    {
        return !isset($context[__CLASS__]) && AbstractAdherentMessage::class === $type;
    }
}
