<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Adherent\Contribution\ContributionPaymentRequest;
use App\Adherent\Contribution\ContributionRequestHandler;
use App\Adherent\Contribution\RevenueDeclarationRequest;
use App\Entity\Adherent;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[IsGranted(new Expression("is_granted('ROLE_OAUTH_SCOPE_READ:PROFILE') and is_granted('ongoing_eletected_representative')"))]
#[Route(path: '/v3/profile', name: 'app_api_user_profile')]
class ElectProfileController extends AbstractController
{
    #[Route(path: '/elect-declaration', methods: ['POST'])]
    public function saveDeclaration(
        ValidatorInterface $validator,
        SerializerInterface $serializer,
        Request $request,
        UserInterface $adherent,
        ContributionRequestHandler $contributionRequestHandler,
    ): Response {
        /** @var RevenueDeclarationRequest $command */
        $command = $serializer->deserialize($request->getContent(), RevenueDeclarationRequest::class, 'json');
        $errors = $validator->validate($command);

        if ($errors->count() > 0) {
            return $this->json($errors, Response::HTTP_BAD_REQUEST);
        }

        /** @var Adherent $adherent */
        $result = $contributionRequestHandler->handleDeclaration($adherent, $command->revenueAmount);

        return new JsonResponse([
            'status' => 'ok',
            'payment_step_required' => $result->paymentStepRequired,
            'current_contribution_amount' => $result->currentContributionAmount,
        ], Response::HTTP_CREATED);
    }

    #[Route(path: '/elect-payment', methods: ['POST'])]
    public function savePayment(
        ValidatorInterface $validator,
        SerializerInterface $serializer,
        Request $request,
        UserInterface $adherent,
        ContributionRequestHandler $contributionRequestHandler,
    ): Response {
        /** @var ContributionPaymentRequest $command */
        $command = $serializer->deserialize($request->getContent(), ContributionPaymentRequest::class, 'json');
        $errors = $validator->validate($command);

        if ($errors->count() > 0) {
            return $this->json($errors, Response::HTTP_BAD_REQUEST);
        }

        /** @var Adherent $adherent */
        $contributionRequestHandler->handle($command, $adherent);

        return new JsonResponse('OK', Response::HTTP_CREATED);
    }

    #[Route(path: '/elect-payment/stop', methods: ['POST'])]
    public function stopPayment(
        UserInterface $adherent,
        ContributionRequestHandler $contributionRequestHandler,
    ): Response {
        /** @var Adherent $adherent */
        $contributionRequestHandler->cancelLastContribution($adherent);

        return new JsonResponse('OK', Response::HTTP_CREATED);
    }
}
