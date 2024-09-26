<?php

namespace App\Normalizer;

use App\Entity\Adherent;
use App\Repository\AdherentChangeEmailTokenRepository;
use App\Repository\Phoning\CampaignHistoryRepository;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class AdherentNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly array $adherentInterests,
        private readonly CampaignHistoryRepository $campaignHistoryRepository,
        private readonly AdherentChangeEmailTokenRepository $changeEmailTokenRepository,
    ) {
    }

    private const LEGACY_MAPPING = [
        'email_address' => 'emailAddress',
        'postal_code' => 'zipCode',
        'first_name' => 'firstName',
        'last_name' => 'lastName',
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
        $groups = $context['groups'] ?? [];

        if (\in_array('legacy', $groups)) {
            $data = $this->addBackwardCompatibilityFields($data);
        }

        if (\in_array('profile_read', $groups)) {
            $interests = [];
            foreach ($object->getInterests() as $interest) {
                $interests[] = [
                    'label' => $this->adherentInterests[$interest],
                    'code' => $interest,
                ];
            }

            $data['interests'] = $interests;

            $data['change_email_token'] = $this->normalizer->normalize(
                $this->changeEmailTokenRepository->findLastUnusedByAdherent($object),
                $format,
                $context
            );
        }

        if (\in_array('phoning_campaign_call_read', $groups)) {
            $lastCall = $this->campaignHistoryRepository->findLastHistoryForAdherent($object);

            $data['info'] = \sprintf(
                '%s%s, habitant %s (%s). %s.',
                $object->getFirstName(),
                $object->getBirthdate() ? \sprintf(', %s ans', $object->getAge()) : '',
                $object->getCityName(),
                $object->getPostalCode(),
                \sprintf($lastCall
                    ? 'Appelé%s précédemment le '.$lastCall->getBeginAt()->format('d/m/Y')
                    : 'N’a encore jamais été appelé%s', $object->isFemale() ? 'e' : '')
            );
        }

        if (\in_array('action_read', $groups)) {
            $data['image_url'] = $this->getImageUrl($object);
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

    protected function getImageUrl(Adherent $adherent): ?string
    {
        return $adherent?->getImageName() ? $this->urlGenerator->generate(
            'asset_url',
            ['path' => $adherent->getImagePath()],
            UrlGeneratorInterface::ABSOLUTE_URL
        ) : null;
    }
}
