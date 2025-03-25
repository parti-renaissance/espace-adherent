<?php

namespace App\Normalizer;

use App\Donation\DonationValueObject;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class DonationValueObjectNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    public function __construct(private readonly TranslatorInterface $translator)
    {
    }

    /**
     * @param DonationValueObject $object
     *
     * @return array
     */
    public function normalize($object, $format = null, array $context = []): array|string|int|float|bool|\ArrayObject|null
    {
        $data = $this->normalizer->normalize($object, $format, $context + [__CLASS__ => true]);
        $groups = $context['groups'] ?? [];

        if (\in_array('donation_read', $groups)) {
            $data['type_label'] = $this->translateDonationType($object->getType());
        }

        return $data;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            DonationValueObject::class => false,
        ];
    }

    public function supportsNormalization($data, $format = null, array $context = []): bool
    {
        return !isset($context[__CLASS__]) && $data instanceof DonationValueObject;
    }

    private function translateDonationType(string $donationType): string
    {
        $key = 'donation.type.'.$donationType;

        $translated = $this->translator->trans($key);

        return $translated !== $key ? $translated : $donationType;
    }
}
