<?php

namespace App\Normalizer;

use App\Entity\Adherent;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class AdherentNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    private $adherentInterests;

    public function __construct(array $adherentInterests)
    {
        $this->adherentInterests = $adherentInterests;
    }

    private const LEGACY_MAPPING = [
        'email_address' => 'emailAddress',
        'postal_code' => 'zipCode',
        'first_name' => 'firstName',
        'last_name' => 'lastName',
        'managed_area_tag_codes' => 'managedAreaTagCodes',
    ];

    protected const ALREADY_CALLED = 'ADHERENT_NORMALIZER_ALREADY_CALLED';

    /**
     * @param Adherent $object
     *
     * @return array
     */
    public function normalize($object, $format = null, array $context = [])
    {
        $context[static::ALREADY_CALLED] = true;

        $data = $this->normalizer->normalize($object, $format, $context);

        if (\in_array('adherent_change_diff', $context['groups'])) {
            $data['city'] = $object->getCityName();
        }

        if (\in_array('legacy', $context['groups'])) {
            $data = $this->addBackwardCompatibilityFields($data);
        }

        if (\in_array('user_profile', $context['groups'])) {
            $interests = [];
            foreach ($object->getInterests() as $interest) {
                $interests[] = [
                    'label' => $this->adherentInterests[$interest],
                    'code' => $interest,
                ];
            }

            $data['interests'] = $interests;
        }

        return $data;
    }

    public function supportsNormalization($data, $format = null, array $context = [])
    {
        return !isset($context[static::ALREADY_CALLED]) && $data instanceof Adherent;
    }

    protected function addBackwardCompatibilityFields(array $data): array
    {
        foreach (self::LEGACY_MAPPING as $newKey => $oldKey) {
            if (\array_key_exists($newKey, $data)) {
                $data[$oldKey] = $data[$newKey];
            }
        }

        return $data;
    }
}
