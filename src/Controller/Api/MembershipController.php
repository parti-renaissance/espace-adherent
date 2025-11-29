<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Membership\AdherentResetPasswordHandler;
use App\Membership\MembershipRequestHandler;
use App\Membership\MembershipSourceEnum;
use App\Repository\AdherentRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class MembershipController extends AbstractController
{
    /**
     * This action enables a guest user to adhere to the community.
     */
    #[Route(path: '/membership', name: 'app_api_membership_register', methods: ['POST'])]
    public function registerAction(
        Request $request,
        AuthorizationCheckerInterface $authorizationChecker,
        SerializerInterface $serializer,
        ValidatorInterface $validator,
        MembershipRequestHandler $handler,
    ): Response {
        if ($authorizationChecker->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            throw $this->createAccessDeniedException('Logged in users can not create account.');
        }

        $membershipRequest = $handler->initialiseMembershipRequest($request->query->get('source', MembershipSourceEnum::JEMENGAGE));

        $serializer->deserialize(
            $request->getContent(),
            $membershipRequest::class,
            JsonEncoder::FORMAT,
            [
                AbstractNormalizer::OBJECT_TO_POPULATE => $membershipRequest,
                AbstractNormalizer::GROUPS => ['membership:write'],
            ]
        );

        $errors = $validator->validate($membershipRequest);

        if ($errors->count() > 0) {
            return $this->json($errors, Response::HTTP_BAD_REQUEST);
        }

        $handler->createAdherent($membershipRequest);

        return $this->json('OK', Response::HTTP_CREATED);
    }

    #[Route(path: '/membership/forgot-password', name: 'app_api_membership_forgot_password', methods: ['POST'])]
    public function forgotPasswordAction(
        Request $request,
        AdherentRepository $adherentRepository,
        AdherentResetPasswordHandler $adherentResetPasswordHandler,
    ): Response {
        $data = json_decode($request->getContent(), true);

        if (!\is_array($data) || empty($data['email_address'])) {
            return $this->json('The field "email_address" is not provided.', Response::HTTP_BAD_REQUEST);
        }

        if ($adherent = $adherentRepository->findOneByEmail($data['email_address'])) {
            $adherentResetPasswordHandler->handle($adherent);
        }

        return $this->json('OK');
    }
}
