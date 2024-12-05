<?php

namespace App\Controller\Api;

use ApiPlatform\Problem\Serializer\ConstraintViolationListNormalizer;
use App\AdherentProfile\Password;
use App\Entity\Adherent;
use App\Entity\AdherentResetPasswordToken;
use App\Exception\AdherentTokenAlreadyUsedException;
use App\Exception\AdherentTokenExpiredException;
use App\Exception\AdherentTokenMismatchException;
use App\Membership\AdherentResetPasswordHandler;
use App\Normalizer\ImageExposeNormalizer;
use App\Normalizer\TranslateAdherentTagNormalizer;
use App\OAuth\Model\ClientApiUser;
use App\OAuth\Model\DeviceApiUser;
use App\OAuth\OAuthTokenGenerator;
use League\OAuth2\Server\Exception\OAuthServerException;
use Nyholm\Psr7\Response as PsrResponse;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

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

        if ($this->isGranted('ROLE_OAUTH_SCOPE_JEMARCHE_APP')) {
            $context['groups'] = ['jemarche_user_profile'];
            $context[TranslateAdherentTagNormalizer::ENABLE_TAG_TRANSLATOR] = true;
            $context[TranslateAdherentTagNormalizer::NO_STATIC_TAGS] = true;
        }

        $context['groups'][] = 'user_profile';
        $context['groups'][] = ImageExposeNormalizer::NORMALIZATION_GROUP;

        return $context;
    }

    #[Route(path: '/profile/mot-de-passe/{user_uuid}/{create_password_token}', name: 'user_create_password', requirements: ['user_uuid' => '%pattern_uuid%', 'reset_password_token' => '%pattern_sha1%'], methods: ['POST'])]
    public function createPassword(
        Request $request,
        #[MapEntity(expr: 'repository.findOneByUuid(user_uuid)')]
        Adherent $user,
        #[MapEntity(expr: 'repository.findByToken(create_password_token)')]
        AdherentResetPasswordToken $createPasswordToken,
        AdherentResetPasswordHandler $handler,
        SerializerInterface $serializer,
        ValidatorInterface $validator,
        OAuthTokenGenerator $authTokenGenerator,
    ) {
        if ($createPasswordToken->getUsageDate()) {
            return $this->createBadRequestResponse('Pas de Token de création de mot de passe disponible');
        }

        /** @var Password $password */
        $password = $serializer->deserialize($request->getContent(), Password::class, JsonEncoder::FORMAT);

        $errors = $validator->validate($password);

        if (0 !== $errors->count()) {
            return JsonResponse::fromJsonString(
                $serializer->serialize($errors, ConstraintViolationListNormalizer::FORMAT),
                Response::HTTP_BAD_REQUEST
            );
        }

        try {
            $handler->reset($user, $createPasswordToken, $password->getPassword());

            if ($clientId = $request->query->get('client_id')) {
                $accessTokenResponse = $authTokenGenerator->generate($request, $user, $clientId, $password->getPassword());
                if (null !== $accessTokenResponse) {
                    return $accessTokenResponse;
                }
            }

            return $this->json('OK');
        } catch (AdherentTokenExpiredException $e) {
            return $this->createBadRequestResponse(
                'Le temps de création de mot de passe est expiré ! Veuillez faire une demande de réinitialisation de mot de passe.'
            );
        } catch (AdherentTokenAlreadyUsedException $e) {
            return $this->createBadRequestResponse('Le changement de mot de passe avec ce token a déjà été fait.');
        } catch (AdherentTokenMismatchException $e) {
            return $this->createBadRequestResponse('Le token ne correspond pas à l\'utilisateur.');
        }
    }

    private function createBadRequestResponse(string $msg): JsonResponse
    {
        return $this->json($msg, Response::HTTP_BAD_REQUEST);
    }
}
