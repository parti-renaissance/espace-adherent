<?php

namespace App\Normalizer;

use App\Entity\AdherentMandate\ElectedRepresentativeAdherentMandate;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class ElectedRepresentativeAdherentMandateNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    public function __construct(private readonly TranslatorInterface $translator)
    {
    }

    protected const ALREADY_CALLED = 'ELECTED_REPRESENTATIVE_ADHERENT_MANDATE_NORMALIZER_ALREADY_CALLED';

    /**
     * @param ElectedRepresentativeAdherentMandate $object
     *
     * @return array
     */
    public function normalize($object, $format = null, array $context = []): array|string|int|float|bool|\ArrayObject|null
    {
        $data = $this->normalizer->normalize($object, $format, $context + [__CLASS__ => true]);
        $groups = $context['groups'] ?? [];

        if (\in_array('adherent_elect_read', $groups)) {
            $data['mandate_type_label'] = $this->translateMandateType($object->mandateType);
        }

        return $data;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            ElectedRepresentativeAdherentMandate::class => false,
        ];
    }

    public function supportsNormalization($data, $format = null, array $context = []): bool
    {
        return !isset($context[__CLASS__]) && $data instanceof ElectedRepresentativeAdherentMandate;
    }

    private function translateMandateType(string $mandateType): string
    {
        $key = 'adherent.mandate.type.'.$mandateType;

        $translated = $this->translator->trans($key);

        return $translated !== $key ? $translated : $mandateType;
    }
}
