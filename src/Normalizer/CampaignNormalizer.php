<?php

namespace App\Normalizer;

use App\Entity\Phoning\Campaign;
use App\Repository\AdherentRepository;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class CampaignNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    private const CAMPAIGN_ALREADY_CALLED = 'CAMPAIGN_NORMALIZER_ALREADY_CALLED';

    private $adherentRepository;
    private $tokenStorage;

    public function __construct(AdherentRepository $adherentRepository, TokenStorageInterface $tokenStorage)
    {
        $this->adherentRepository = $adherentRepository;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * @param Campaign $object
     */
    public function normalize($object, $format = null, array $context = [])
    {
        $context[self::CAMPAIGN_ALREADY_CALLED] = true;

        $campaign = $this->normalizer->normalize($object, $format, $context);

        $caller = $this->tokenStorage->getToken()->getUser();
        $adherents = $this->adherentRepository->findScoresByCampaign($object);
        $callers = [];
        $adherentAdded = false;
        // add top 3, if caller in top 3 OR top 2 and a caller
        foreach ($adherents as $key => $adherent) {
            $adherent['position'] = ++$key;
            $adherent['caller'] = $caller->getId() === $adherent['id'];
            unset($adherent['id']);

            if ($adherent['caller']) {
                $callers[] = $adherent;
                $adherentAdded = true;
            } elseif ((\count($callers) < 3 && $adherentAdded) || \count($callers) < 2) {
                $callers[] = $adherent;
            }

            if (3 === \count($callers)) {
                break;
            }
        }

        $campaign['nb_calls'] = $object->getCampaignHistories()->count();
        $campaign['nb_surveys'] = $object->getCampaignHistoriesWithDataSurvey()->count();
        $campaign['scoreboard'] = $callers;

        return $campaign;
    }

    public function supportsNormalization($data, $format = null, array $context = [])
    {
        return
            empty($context[self::CAMPAIGN_ALREADY_CALLED])
            && $data instanceof Campaign
            && \in_array('phoning_campaign_read', $context['groups'] ?? [])
        ;
    }
}
