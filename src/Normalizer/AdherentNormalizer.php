<?php

namespace App\Normalizer;

use App\Adherent\AdherentInstances;
use App\Entity\Adherent;
use App\OAuth\Model\Scope;
use App\Repository\AdherentChangeEmailTokenRepository;
use App\Repository\Jecoute\DataSurveyRepository;
use App\Repository\OAuth\ClientRepository;
use App\Repository\Phoning\CampaignHistoryRepository;
use App\Security\Voter\DataCornerVoter;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class AdherentNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    public function __construct(
        private readonly array $adherentInterests,
        private readonly CampaignHistoryRepository $campaignHistoryRepository,
        private readonly AdherentChangeEmailTokenRepository $changeEmailTokenRepository,
        private readonly AdherentInstances $adherentInstances,
        private readonly DataSurveyRepository $dataSurveyRepository,
        private readonly AuthorizationCheckerInterface $authorizationChecker,
        private readonly ClientRepository $clientRepository,
        private readonly string $referralHost,
    ) {
    }

    private const LEGACY_MAPPING = [
        'email_address' => 'emailAddress',
        'postal_code' => 'zipCode',
        'first_name' => 'firstName',
        'last_name' => 'lastName',
    ];

    /**
     * @param Adherent $object
     *
     * @return array
     */
    public function normalize($object, $format = null, array $context = []): array|string|int|float|bool|\ArrayObject|null
    {
        $data = $this->normalizer->normalize($object, $format, $context + [__CLASS__ => true]);
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

        if (\in_array('user_profile', $groups)) {
            $data['instances'] = $this->adherentInstances->generate($object);
            $data['referral_link'] = $object->getPublicId() ? rtrim($this->referralHost, '/').'/'.$object->getPublicId() : null;
        }

        if (\in_array('jemarche_user_profile', $groups)) {
            $data['surveys'] = [
                'total' => $this->dataSurveyRepository->countByAdherent($object),
                'last_month' => $this->dataSurveyRepository->countByAdherentForLastMonth($object),
            ];

            if ($data['cadre_access'] = $this->authorizationChecker->isGranted(DataCornerVoter::DATA_CORNER, $object)) {
                $client = $this->clientRepository->getCadreClient();
                $data['cadre_auth_path'] = '/oauth/v2/auth?scope='.Scope::JEMENGAGE_ADMIN.'&response_type=code&client_id='.$client->getUuid().'&redirect_uri='.urlencode($client->getRedirectUris()[0]);
            }
        }

        return $data;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            '*' => null,
            Adherent::class => false,
        ];
    }

    public function supportsNormalization($data, $format = null, array $context = []): bool
    {
        return !isset($context[__CLASS__]) && $data instanceof Adherent;
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
