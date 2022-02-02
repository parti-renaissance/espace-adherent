<?php

namespace App\Normalizer;

use App\Entity\Adherent;
use App\Entity\Event\BaseEvent;
use App\Repository\EventRegistrationRepository;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class EventUserRegistrationNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    public const EVENT_USER_REGISTRATION_ALREADY_CALLED = 'event_user_registration_normalizer';

    private Security $security;
    private EventRegistrationRepository $eventRegistrationRepository;

    public function __construct(Security $security, EventRegistrationRepository $eventRegistrationRepository)
    {
        $this->security = $security;
        $this->eventRegistrationRepository = $eventRegistrationRepository;
    }

    public function normalize($object, $format = null, array $context = [])
    {
        $context[self::EVENT_USER_REGISTRATION_ALREADY_CALLED] = true;

        $event = $this->normalizer->normalize($object, $format, $context);

        $registration = $this->eventRegistrationRepository->findAdherentRegistration(
            $object->getUuid()->toString(),
            $this->security->getUser()->getUuid()->toString()
        );

        $event['user_registered_at'] = $registration ? $registration->getCreatedAt()->format(\DateTime::RFC3339) : null;

        return $event;
    }

    public function supportsNormalization($data, $format = null, array $context = [])
    {
        return
            empty($context[self::EVENT_USER_REGISTRATION_ALREADY_CALLED])
            && $data instanceof BaseEvent
            && \in_array('with_user_registration', $context['groups'] ?? [])
            && $this->security->getUser() instanceof Adherent
        ;
    }
}
