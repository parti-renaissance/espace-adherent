<?php

namespace App\Normalizer;

use App\Entity\Adherent;
use App\Entity\Event\BaseEvent;
use App\Repository\EventRegistrationRepository;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class EventNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    public const EVENT_USER_REGISTRATION_ALREADY_CALLED = 'event_normalizer';

    public function __construct(
        private readonly Security $security,
        private readonly EventRegistrationRepository $eventRegistrationRepository
    ) {
    }

    /** @param BaseEvent $object */
    public function normalize($object, $format = null, array $context = [])
    {
        $context[self::EVENT_USER_REGISTRATION_ALREADY_CALLED] = true;

        $event = $this->normalizer->normalize($object, $format, $context);

        if ($this->security->getUser() instanceof Adherent) {
            if (\in_array('with_user_registration', $context['groups'] ?? [])) {
                $registration = $this->eventRegistrationRepository->findAdherentRegistration(
                    $object->getUuid()->toString(),
                    $this->security->getUser()->getUuid()->toString()
                );

                $event['user_registered_at'] = $registration?->getCreatedAt()->format(\DateTimeInterface::RFC3339);
            }
        } elseif (!$object->isPublic()) {
            $event = $this->cleanPrivateEvent($event);
        }

        return $event;
    }

    public function supportsNormalization($data, $format = null, array $context = []): bool
    {
        return
            empty($context[self::EVENT_USER_REGISTRATION_ALREADY_CALLED])
            && $data instanceof BaseEvent;
    }

    private function cleanPrivateEvent(array $event): array
    {
        foreach ($event as $key => $value) {
            if (!\in_array($key, ['name', 'uuid', 'slug', 'time_zone', 'begin_at', 'finish_at', 'status', 'visibility', 'image_url', 'link'])) {
                $event[$key] = null;
            }
        }

        return $event;
    }
}
