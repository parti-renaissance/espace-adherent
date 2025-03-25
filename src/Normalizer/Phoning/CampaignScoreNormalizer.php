<?php

namespace App\Normalizer\Phoning;

use App\Entity\Adherent;
use App\Entity\Phoning\Campaign;
use App\Repository\AdherentRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class CampaignScoreNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    public function __construct(
        private readonly AdherentRepository $adherentRepository,
        private readonly Security $security,
    ) {
    }

    /**
     * @param Campaign $object
     */
    public function normalize($object, $format = null, array $context = []): array|string|int|float|bool|\ArrayObject|null
    {
        $campaign = $this->normalizer->normalize($object, $format, $context + [__CLASS__ => true]);

        /** @var Adherent $caller */
        $caller = $this->security->getUser();
        $callerId = $caller->getId();

        if (isset($context['operation_name']) && '_api_/v3/phoning_campaigns/{uuid}/scores_get' === $context['operation_name']) {
            $campaign['nb_calls'] = $object->getCampaignHistoriesForAdherent($caller)->count();
            $campaign['nb_surveys'] = $object->getCampaignHistoriesWithDataSurveyForAdherent($caller)->count();
        } else {
            $campaign['nb_calls'] = $object->getCampaignHistories()->count();
            $campaign['nb_surveys'] = $object->getCampaignHistoriesWithDataSurvey()->count();
        }

        $callers = $this->adherentRepository->findScoresByCampaign($object);
        array_walk($callers, function (&$adherent, $key) use ($callerId) {
            $adherent['caller'] = $callerId === $adherent['id'];
            $adherent['position'] = ++$key;
            unset($adherent['id']);
        });
        $campaign['scoreboard'] = $callers;

        return $campaign;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            Campaign::class => false,
        ];
    }

    public function supportsNormalization($data, $format = null, array $context = []): bool
    {
        return
            !isset($context[__CLASS__])
            && $data instanceof Campaign
            && \in_array('phoning_campaign_read_with_score', $context['groups'] ?? []);
    }
}
