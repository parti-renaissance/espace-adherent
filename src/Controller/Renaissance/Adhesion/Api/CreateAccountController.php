<?php

declare(strict_types=1);

namespace App\Controller\Renaissance\Adhesion\Api;

use App\Adhesion\Command\CreateAccountCommand;
use App\Adhesion\CreateAdherentResult;
use App\Adhesion\Request\MembershipRequest;
use App\Controller\Renaissance\Adhesion\ActivateEmailController;
use App\Entity\Adherent;
use App\Security\AdherentLogin;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\HandleTrait;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route(path: '/api/create-account', name: 'app_adhesion_create_account', methods: ['POST'])]
class CreateAccountController extends AbstractController
{
    use HandleTrait;

    public function __construct(
        private readonly ValidatorInterface $validator,
        private readonly SerializerInterface $serializer,
        private readonly AdherentLogin $adherentLogin,
        MessageBusInterface $messageBus,
    ) {
        $this->messageBus = $messageBus;
    }

    public function __invoke(Request $request): Response
    {
        /** @var Adherent|null $currentUser */
        if ($currentUser = $this->getUser()) {
            $emailIdentifier = $currentUser->getEmailAddress();
        } elseif (!$emailIdentifier = $request->getSession()->get(PersistEmailController::SESSION_KEY)) {
            return $this->json([
                'message' => 'Validation Failed',
                'status' => 'error',
                'violations' => [[
                    'propertyPath' => 'email',
                    'message' => 'Veuillez valider cette première étape du parcours.',
                ]],
            ], Response::HTTP_BAD_REQUEST);
        }

        $membershipRequest = $this->serializer->deserialize($request->getContent(), MembershipRequest::class, JsonEncoder::FORMAT);

        if (!$currentUser && $membershipRequest->email !== $emailIdentifier) {
            return $this->json([
                'message' => 'Validation Failed',
                'status' => 'error',
                'violations' => [[
                    'propertyPath' => 'email',
                    'message' => 'L’adresse email renseignée ne correspond pas à celle renseignée précédemment.',
                ]],
            ], Response::HTTP_BAD_REQUEST);
        }

        $errors = $this->validator->validate($membershipRequest, null, ['Default', 'adhesion']);

        if ($errors->count()) {
            return $this->json($errors, Response::HTTP_BAD_REQUEST);
        }

        if ($membershipRequest->referral) {
            $membershipRequest->originalEmail = $emailIdentifier;
        }

        $result = $this->handle(new CreateAccountCommand($membershipRequest, $currentUser));

        if ($result instanceof CreateAdherentResult && $result->getAdherent()) {
            $this->adherentLogin->login($result->getAdherent());

            if ($result->isNextStepPayment()) {
                return $this->json([
                    'message' => 'OK',
                    'status' => 'success',
                ], Response::HTTP_CREATED);
            }

            if ($result->isNextStepActivation()) {
                return $this->json([
                    'message' => 'Vous allez être redirigé vers la suite du parcours d’adhésion.',
                    'status' => 'redirect',
                    'location' => $this->generateUrl(ActivateEmailController::ROUTE_NAME),
                ], Response::HTTP_OK);
            }
        }

        return $this->json([
            'message' => 'Un email de confirmation vient d’être envoyé à votre adresse email. Cliquez sur le lien de validation qu’il contient pour continuer votre adhésion.',
            'status' => 'warning',
        ], Response::HTTP_OK);
    }
}
