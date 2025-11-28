<?php

declare(strict_types=1);

namespace App\Normalizer;

use App\Adherent\Referral\IdentifierGenerator;
use App\Adherent\Referral\ModeEnum;
use App\Adherent\Referral\StatusEnum;
use App\Adherent\Referral\TypeEnum;
use App\Entity\Adherent;
use App\Entity\Referral;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

final class ReferralDenormalizer implements DenormalizerInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;

    public function __construct(
        private readonly Security $security,
        private readonly IdentifierGenerator $referralIdentifierGenerator,
    ) {
    }

    public function denormalize($data, $class, $format = null, array $context = []): mixed
    {
        /** @var Referral $data */
        $data = $this->denormalizer->denormalize($data, $class, $format, $context + [__CLASS__ => true]);

        if (!$data->getId()) {
            $data->referrer = $this->getCurrentUser();

            $data->identifier = $this->referralIdentifierGenerator->generate();

            $data->type = $data->hasFullInformations()
                ? TypeEnum::PREREGISTRATION
                : TypeEnum::INVITATION;

            $data->mode = ModeEnum::EMAIL;

            $data->status = StatusEnum::INVITATION_SENT;
        }

        return $data;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            Referral::class => false,
        ];
    }

    public function supportsDenormalization($data, $type, $format = null, array $context = []): bool
    {
        return !isset($context[__CLASS__])
            && is_a($type, Referral::class, true)
            && $this->security->getUser() instanceof Adherent;
    }

    private function getCurrentUser(): ?Adherent
    {
        return $this->security->getUser();
    }
}
