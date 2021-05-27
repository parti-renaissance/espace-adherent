<?php

namespace App\Normalizer;

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

    private const ADHERENT_MESSAGE_DENORMALIZER_ALREADY_CALLED = 'ADHERENT_MESSAGE_DENORMALIZER_ALREADY_CALLED';

    public function denormalize($data, $type, $format = null, array $context = [])
    {
        if (!empty($context[AbstractNormalizer::OBJECT_TO_POPULATE])) {
            $messageClass = \get_class($context[AbstractNormalizer::OBJECT_TO_POPULATE]);
        } else {
            $messageType = $data['type'] ?? null;

            if (!$messageType || !($messageClass = $this->getMessageClassFromType($messageType))) {
                throw new UnexpectedValueException('Type value is missing or invalid');
            }
        }

        if (!$messageClass) {
            throw new UnexpectedValueException('Type value is missing or invalid');
        }

        unset($data['type']);

        $context[self::ADHERENT_MESSAGE_DENORMALIZER_ALREADY_CALLED] = true;

        /** @var AdherentMessageInterface $message */
        $message = $this->denormalizer->denormalize($data, $messageClass, $format, $context);

        $message->setSource(AdherentMessageInterface::SOURCE_API);

        return $message;
    }

    public function supportsDenormalization($data, $type, $format = null, array $context = [])
    {
        return
            empty($context[self::ADHERENT_MESSAGE_DENORMALIZER_ALREADY_CALLED])
            && AbstractAdherentMessage::class === $type;
    }

    private function getMessageClassFromType(string $messageType): ?string
    {
        return AdherentMessageFactory::getMessageClassName($messageType);
    }
}
