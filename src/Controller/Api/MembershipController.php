<?php

namespace App\Controller\Api;

use App\Membership\LightMembershipRequest;
use App\Membership\MembershipRequestHandler;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class MembershipController extends AbstractController
{
    /**
     * This action enables a guest user to adhere to the community.
     *
     * @Route("/membership", name="app_api_membership_register", methods={"POST"})
     */
    public function registerAction(
        Request $request,
        AuthorizationCheckerInterface $authorizationChecker,
        SerializerInterface $serializer,
        ValidatorInterface $validator,
        MembershipRequestHandler $handler,
        EntityManagerInterface $manager
    ): Response {
        if ($authorizationChecker->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException('Logged in users can not create account.');
        }

        /** @var LightMembershipRequest $membershipRequest */
        $membershipRequest = $serializer->deserialize($request->getContent(), LightMembershipRequest::class, JsonEncoder::FORMAT);

        $errors = $validator->validate($membershipRequest);

        if ($errors->count() > 0) {
            return $this->json($errors, Response::HTTP_BAD_REQUEST);
        }

        $handler->registerLightUser($membershipRequest);
        $manager->flush();

        return $this->json('OK', Response::HTTP_CREATED);
    }
}
