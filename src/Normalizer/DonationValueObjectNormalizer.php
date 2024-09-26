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

    public function __construct(
        private readonly TranslatorInterface $translator,
    ) {
    }

    protected const ALREADY_CALLED = 'DONATION_VALUE_OBJECT_NORMALIZER_ALREADY_CALLED';

    /**
     * @param DonationValueObject $object
     *
     * @return array
     */
    public function normalize($object, $format = null, array $context = [])
    {
        $context[static::ALREADY_CALLED] = true;

        $data = $this->normalizer->normalize($object, $format, $context);
        $groups = $context['groups'] ?? [];

        if (\in_array('donation_read', $groups)) {
            $data['type_label'] = $this->translateDonationType($object->getType());
        }

        return $data;
    }

    public function supportsNormalization($data, $format = null, array $context = [])
    {
        return !isset($context[static::ALREADY_CALLED]) && $data instanceof DonationValueObject;
    }

    private function translateDonationType(string $donationType): string
    {
        $key = 'donation.type.'.$donationType;

        $translated = $this->translator->trans($key);

        return $translated !== $key ? $translated : $donationType;
    }
}
