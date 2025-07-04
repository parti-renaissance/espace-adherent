<?php

namespace App\Validator;

use App\Event\Request\EventInscriptionRequest;
use App\Repository\NationalEvent\EventInscriptionRepository;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class NationalEventTransportModeValidator extends ConstraintValidator
{
    public function __construct(private readonly EventInscriptionRepository $eventInscriptionRepository)
    {
    }

    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof NationalEventTransportMode) {
            throw new UnexpectedTypeException($constraint, NationalEventTransportMode::class);
        }

        if (!$value) {
            return;
        }

        if (!$value instanceof EventInscriptionRequest) {
            throw new UnexpectedValueException($value, EventInscriptionRequest::class);
        }

        $transportConfig = $value->transportConfiguration;

        if (!$value->visitDay || !$value->transport || !$transportConfig || empty($transportConfig['transports'])) {
            return;
        }

        $availableModes = array_filter(
            $transportConfig['transports'],
            static function (array $transport) use ($value) {
                return \in_array($value->visitDay, $transport['jours_ids'] ?? [], true);
            }
        );

        if (!\in_array($value->transport, array_column($availableModes, 'id'), true)) {
            $this
                ->context
                ->buildViolation($constraint->messageInvalidTransport)
                ->atPath('transport')
                ->addViolation()
            ;
        }

        $selectedTransportConfig = null;
        foreach ($availableModes as $transport) {
            if ($transport['id'] === $value->transport) {
                $selectedTransportConfig = $transport;
                break;
            }
        }

        if ($selectedTransportConfig && isset($selectedTransportConfig['quota'])) {
            $reservedPlaces = $this->eventInscriptionRepository->countPlacesByTransport($value->eventId, [$value->transport]);

            if ($selectedTransportConfig['quota'] <= ($reservedPlaces[$value->transport] ?? 0)) {
                $this
                    ->context
                    ->buildViolation($constraint->messageTransportLimit)
                    ->atPath('transport')
                    ->addViolation()
                ;
            }
        }

        $availableAccommodationModes = array_filter(
            $transportConfig['hebergements'] ?? [],
            static function (array $accommodation) use ($value) {
                return \in_array($value->visitDay, $accommodation['jours_ids'] ?? [], true);
            }
        );

        if (empty($availableAccommodationModes)) {
            return;
        }

        if (empty($value->accommodation)) {
            $this
                ->context
                ->buildViolation($constraint->messageAccommodationMissing)
                ->atPath('accommodation')
                ->addViolation()
            ;

            return;
        }

        if (!\in_array($value->accommodation, array_column($availableAccommodationModes, 'id'), true)) {
            $this
                ->context
                ->buildViolation($constraint->messageInvalidAccommodation)
                ->atPath('accommodation')
                ->addViolation()
            ;
        }
    }
}
