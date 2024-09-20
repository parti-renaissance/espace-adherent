<?php

namespace App\Normalizer;

use App\AppCodeEnum;
use App\OAuth\Model\Scope;
use App\Repository\Jecoute\DataSurveyRepository;
use App\Repository\OAuth\ClientRepository;
use App\Security\Voter\DataCornerVoter;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
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
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly string $adminRenaissanceHost,
        private readonly string $userVoxHost,
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
            if ($object->getAuthAppVersion() < 580) {
                $data['cadre_auth_path'] = $this->urlGenerator->generate('app_front_oauth_authorize', [
                    'scope' => Scope::JEMENGAGE_ADMIN,
                    'response_type' => 'code',
                    'client_id' => $this->clientRepository->findOneBy(['code' => AppCodeEnum::JEMENGAGE_WEB])->getUuid(),
                ]);
            } else {
                $data['cadre_auth_url'] = $this->urlGenerator->generate('app_front_oauth_authorize', [
                    'app_domain' => $this->authorizationChecker->isGranted('ROLE_PREVIOUS_ADMIN') ? $this->adminRenaissanceHost : $this->userVoxHost,
                    'scope' => Scope::JEMENGAGE_ADMIN,
                    'response_type' => 'code',
                    'client_id' => $this->clientRepository->findOneBy(['code' => AppCodeEnum::JEMENGAGE_WEB])->getUuid(
                    ),
                ], UrlGeneratorInterface::ABSOLUTE_URL);
            }
        }

        return $data;
    }

    public function supportsNormalization($data, $format = null, array $context = [])
    {
        return parent::supportsNormalization($data, $format, $context)
            && \in_array('jemarche_user_profile', $context['groups'] ?? []);
    }
}
