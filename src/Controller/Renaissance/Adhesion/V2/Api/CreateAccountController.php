<?php

namespace App\Controller\Renaissance\Adhesion\V2\Api;

use App\Adhesion\Command\CreateAccountCommand;
use App\Adhesion\CreateAdherentResult;
use App\Adhesion\Request\MembershipRequest;
use App\Security\AuthenticationUtils;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\HandleTrait;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
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
        private readonly TokenStorageInterface $tokenStorage,
        private readonly AuthenticationUtils $authenticationUtils,
        MessageBusInterface $messageBus,
    ) {
        $this->messageBus = $messageBus;
    }

    public function __invoke(Request $request): Response
    {
        if (!$emailIdentifier = $request->getSession()->get(PersistEmailController::SESSION_KEY)) {
            return $this->json([
                'message' => 'Validation Failed',
                'status' => 'error',
                'violations' => [[
                    'property' => 'email',
                    'message' => 'Veuillez valider cette première étape du parcours.',
                ]],
            ], Response::HTTP_BAD_REQUEST);
        }

        $membershipRequest = $this->serializer->deserialize($request->getContent(), MembershipRequest::class, JsonEncoder::FORMAT);

        if ($membershipRequest->email !== $emailIdentifier) {
            return $this->json([
                'message' => 'Validation Failed',
                'status' => 'error',
                'violations' => [[
                    'property' => 'email',
                    'message' => 'L’adresse email renseignée ne correspond pas à celle renseignée précédemment.',
                ]],
            ], Response::HTTP_BAD_REQUEST);
        }

        $errors = $this->validator->validate($membershipRequest);

        if ($errors->count()) {
            return $this->json($errors, Response::HTTP_BAD_REQUEST);
        }

        $result = $this->handle(new CreateAccountCommand($membershipRequest, $this->getUser()));

        if ($result instanceof CreateAdherentResult) {
            $this->tokenStorage->setToken(null);
            $request->getSession()->invalidate();
            $this->authenticationUtils->authenticateAdherent($result->getAdherent());

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
                    'location' => $this->generateUrl('app_adhesion_confirm_email'),
                ], Response::HTTP_OK);
            }
        }

        return $this->json([
            'message' => 'Un email de confirmation vient d’être envoyé à votre adresse email. Cliquez sur le lien de validation qu’il contient pour continuer votre adhésion.',
            'status' => 'warning',
        ], Response::HTTP_OK);
    }
}
