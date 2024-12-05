<?php

namespace App\Controller\Api;

use App\Adherent\Contribution\ContributionRequest;
use App\Adherent\Contribution\ContributionRequestHandler;
use App\Adherent\Contribution\ContributionStatusEnum;
use App\Adherent\Tag\Command\RefreshAdherentTagCommand;
use App\Entity\Adherent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
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
    public function saveDeclaration(ValidatorInterface $validator, SerializerInterface $serializer, Request $request, UserInterface $adherent, EntityManagerInterface $entityManager, MessageBusInterface $bus): Response
    {
        /** @var ContributionRequest $command */
        $command = $serializer->deserialize($request->getContent(), ContributionRequest::class, 'json');
        $errors = $validator->validate($command, null, ['fill_revenue']);

        if ($errors->count() > 0) {
            return $this->json($errors, Response::HTTP_BAD_REQUEST);
        }

        /** @var Adherent $adherent */
        $adherent->addRevenueDeclaration($command->revenueAmount);

        $adherent->setContributionStatus(
            $command->needContribution()
                ? ContributionStatusEnum::ELIGIBLE
                : ContributionStatusEnum::NOT_ELIGIBLE
        );

        if (!$command->needContribution()) {
            $adherent->setContributedAt(new \DateTime());
        }

        $entityManager->flush();

        $bus->dispatch(new RefreshAdherentTagCommand($adherent->getUuid()));

        return new JsonResponse('OK', Response::HTTP_CREATED);
    }

    #[Route(path: '/elect-payment', methods: ['POST'])]
    public function savePayment(ValidatorInterface $validator, SerializerInterface $serializer, Request $request, UserInterface $adherent, ContributionRequestHandler $contributionRequestHandler): Response
    {
        $command = $serializer->deserialize($request->getContent(), ContributionRequest::class, 'json');
        $errors = $validator->validate($command, null, ['fill_contribution_informations']);

        if ($errors->count() > 0) {
            return $this->json($errors, Response::HTTP_BAD_REQUEST);
        }

        /** @var Adherent $adherent */
        $command->updateFromAdherent($adherent);
        $contributionRequestHandler->handle($command, $adherent);

        return new JsonResponse('OK', Response::HTTP_CREATED);
    }

    #[Route(path: '/elect-payment/stop', methods: ['POST'])]
    public function stopPayment(UserInterface $adherent, ContributionRequestHandler $contributionRequestHandler): Response
    {
        /** @var Adherent $adherent */
        $contributionRequestHandler->cancelLastContribution($adherent);

        return new JsonResponse('OK', Response::HTTP_CREATED);
    }
}
