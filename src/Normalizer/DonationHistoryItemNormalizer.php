<?php

declare(strict_types=1);

namespace App\Normalizer;

use App\Donation\DonationHistoryItem;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class DonationHistoryItemNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    public function __construct(private readonly TranslatorInterface $translator)
    {
    }

    public function normalize($object, $format = null, array $context = []): array|string|int|float|bool|\ArrayObject|null
    {
        $data = $this->normalizer->normalize($object, $format, $context + [__CLASS__ => true]);
        $groups = $context['groups'] ?? [];

        if (\is_array($groups) && \in_array('donation_read', $groups)) {
            $data['type_label'] = $object->getTypeEnum()->trans($this->translator);
            $data['transaction_type_label'] = $this->translator->trans('donation.transaction_type.'.$object->getTransactionType());
            $data['status_label'] = $object->getStatusEnum()->trans($this->translator);
        }

        return $data;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            DonationHistoryItem::class => false,
        ];
    }

    public function supportsNormalization($data, $format = null, array $context = []): bool
    {
        return !isset($context[__CLASS__]) && $data instanceof DonationHistoryItem;
    }
}
