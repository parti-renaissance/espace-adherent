<?php

namespace App\Normalizer;

use App\OAuth\Model\Scope;
use App\Repository\Jecoute\DataSurveyRepository;
use App\Repository\OAuth\ClientRepository;
use App\Security\Voter\DataCornerVoter;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;

class JecouteAdherentNormalizer extends AdherentNormalizer
{
    use NormalizerAwareTrait;

    protected const ALREADY_CALLED = 'JECOUTE_ADHERENT_NORMALIZER_ALREADY_CALLED';

    public function __construct(
        private readonly DataSurveyRepository $dataSurveyRepository,
        private readonly AuthorizationCheckerInterface $authorizationChecker,
        private readonly ClientRepository $clientRepository,
    ) {
    }

    public function normalize($object, $format = null, array $context = [])
    {
        $data = parent::normalize($object, $format, $context);

        $data['surveys'] = [
            'total' => $this->dataSurveyRepository->countByAdherent($object),
            'last_month' => $this->dataSurveyRepository->countByAdherentForLastMonth($object),
        ];

        if ($data['cadre_access'] = $this->authorizationChecker->isGranted(DataCornerVoter::DATA_CORNER, $object)) {
            $client = $this->clientRepository->getCadreClient();
            $data['cadre_auth_path'] = '/oauth/v2/auth?scope='.Scope::JEMENGAGE_ADMIN.'&response_type=code&client_id='.$client->getUuid().'&redirect_uri='.urlencode($client->getRedirectUris()[0]);
        }

        return $data;
    }

    public function supportsNormalization($data, $format = null, array $context = [])
    {
        return parent::supportsNormalization($data, $format, $context)
            && \in_array('jemarche_user_profile', $context['groups'] ?? []);
    }
}
