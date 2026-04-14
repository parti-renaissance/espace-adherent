<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Entity\Adherent;
use App\Normalizer\ImageExposeNormalizer;
use App\Normalizer\ProfileManagedZoneNormalizer;
use App\Normalizer\TranslateAdherentTagNormalizer;
use App\OAuth\Model\ClientApiUser;
use App\OAuth\Model\DeviceApiUser;
use App\OAuth\Model\Scope;
use League\OAuth2\Server\Exception\OAuthServerException;
use Nyholm\Psr7\Response as PsrResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;

class UserController extends AbstractController
{
    #[Route(path: '/me', name: 'app_api_user_show_me_for_oauth', methods: ['GET'])]
    public function oauthShowMe()
    {
        /** @var Adherent|DeviceApiUser $user */
        $user = $this->getUser();

        if ($user instanceof ClientApiUser) {
            return OAuthServerException::accessDenied('API user does not have access to this route')
                ->generateHttpResponse(new PsrResponse())
            ;
        }

        if ($user instanceof DeviceApiUser) {
            $user = $user->getDevice();
        }

        return $this->json($user, context: $this->getGrantedNormalizationContext());
    }

    private function getGrantedNormalizationContext(): array
    {
        $context = ['groups' => ['legacy']];

        if ($this->isGranted(Scope::generateRole(Scope::JEMARCHE_APP))) {
            $context['groups'] = ['jemarche_user_profile'];
            $context[TranslateAdherentTagNormalizer::ENABLE_TAG_TRANSLATOR] = true;
            $context[TranslateAdherentTagNormalizer::NO_STATIC_TAGS] = true;
        }

        if ($this->isGranted(Scope::generateRole(Scope::READ_PROFILE_MANAGED_ZONE))) {
            $context['groups'][] = ProfileManagedZoneNormalizer::GROUP;
        }

        $context['groups'][] = 'user_profile';
        $context['groups'][] = ImageExposeNormalizer::NORMALIZATION_GROUP;

        return $context;
    }
}
