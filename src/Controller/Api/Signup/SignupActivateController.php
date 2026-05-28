<?php

declare(strict_types=1);

namespace App\Controller\Api\Signup;

use App\Adhesion\ActivationCodeManager;
use App\Adhesion\Exception\ActivationCodeExceptionInterface;
use App\Entity\Adherent;
use App\Membership\Signup\Request\SignupActivateRequest;
use App\Repository\AdherentRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/signup/activate', name: 'api_signup_activate', methods: ['POST'])]
class SignupActivateController extends AbstractSignupController
{
    private const UNIFORM_ERROR = 'invalid_or_expired';

    public function __construct(
        private readonly AdherentRepository $adherentRepository,
        private readonly ActivationCodeManager $activationCodeManager,
        private readonly RateLimiterFactory $signupCodeAttemptLimiter,
    ) {
    }

    public function __invoke(
        Request $request,
        #[MapRequestPayload(serializationContext: ['groups' => ['signup:write']])]
        SignupActivateRequest $payload,
    ): Response {
        $this->enforceIpRateLimit($this->signupCodeAttemptLimiter, $request);

        $adherent = $this->adherentRepository->findOneByEmail((string) $payload->email);

        if (!$adherent instanceof Adherent || !$adherent->isPending()) {
            return $this->json(['error' => self::UNIFORM_ERROR], Response::HTTP_BAD_REQUEST);
        }

        try {
            $this->activationCodeManager->validate((string) $payload->code, $adherent);
        } catch (ActivationCodeExceptionInterface) {
            return $this->json(['error' => self::UNIFORM_ERROR], Response::HTTP_BAD_REQUEST);
        }

        return new Response('', Response::HTTP_NO_CONTENT);
    }
}
