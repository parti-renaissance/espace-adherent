<?php

namespace App\Normalizer;

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
    ) {
    }

    public function denormalize($data, $class, $format = null, array $context = []): mixed
    {
        /** @var Referral $data */
        $data = $this->denormalizer->denormalize($data, $class, $format, $context + [__CLASS__ => true]);

        if (!$data->getId()) {
            /** @var Adherent $currentUser */
            $currentUser = $this->security->getUser();

            $data->referrer = $currentUser;
        }

        return $data;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            '*' => null,
            Referral::class => false,
        ];
    }

    public function supportsDenormalization($data, $type, $format = null, array $context = []): bool
    {
        return !isset($context[__CLASS__])
            && is_a($type, Referral::class, true)
            && $this->security->getUser() instanceof Adherent;
    }
}
