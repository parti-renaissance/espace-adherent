<?php

namespace App\Normalizer;

use App\Entity\Referral;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class ReferralNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    public function __construct(private readonly TranslatorInterface $translator)
    {
    }

    /** @param Referral $object */
    public function normalize($object, $format = null, array $context = []): array|string|int|float|bool|\ArrayObject|null
    {
        $data = $this->normalizer->normalize($object, $format, $context + [__CLASS__ => true]);

        if ($object->isAdhesionFinished()) {
            $data['email_address'] = null;
            $data['phone'] = null;
        }

        $data['type_label'] = $data['type'] ? $this->translator->trans('referral.type.'.$data['type']) : null;
        $data['mode_label'] = $data['mode'] ? $this->translator->trans('referral.mode.'.$data['mode']) : null;
        $data['status_label'] = $data['status'] ? $this->translator->trans('referral.status.'.$data['status']) : null;

        return $data;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            '*' => true,
            Referral::class => false,
        ];
    }

    public function supportsNormalization($data, $format = null, array $context = []): bool
    {
        return !isset($context[__CLASS__]) && $data instanceof Referral;
    }
}
