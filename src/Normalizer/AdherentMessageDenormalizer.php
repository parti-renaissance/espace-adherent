<?php

namespace App\Normalizer;

use App\AdherentMessage\AdherentMessageFactory;
use App\AdherentMessage\AdherentMessageTypeEnum;
use App\Entity\AdherentMessage\AbstractAdherentMessage;
use App\Entity\AdherentMessage\AdherentMessageInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Serializer\Exception\UnexpectedValueException;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class AdherentMessageDenormalizer implements DenormalizerInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;

    private const ADHERENT_MESSAGE_DENORMALIZER_ALREADY_CALLED = 'ADHERENT_MESSAGE_DENORMALIZER_ALREADY_CALLED';

    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

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

        if (isset($data['audience'])) {
            $audienceClass = AdherentMessageTypeEnum::AUDIENCE_CLASSES[$messageType];
            if (!$audienceClass) {
                throw new UnexpectedValueException('No audience class for this message type');
            }

            $audience = $this->entityManager->getRepository($audienceClass)->findByUuid($data['audience']);
            if (!$audience) {
                throw new \InvalidArgumentException(sprintf('Audience with uuid "%s" does not exist', $data['audience']));
            }

            $data['audience'] = $audience;
        }

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
