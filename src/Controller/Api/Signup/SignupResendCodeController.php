<?php

declare(strict_types=1);

namespace App\Controller\Api\Signup;

use App\Entity\Adherent;
use App\Membership\Signup\Command\SendSignupConfirmationCommand;
use App\Membership\Signup\Request\SignupResendCodeRequest;
use App\RateLimiter\ExponentialBackoffPolicy;
use App\Repository\AdherentActivationCodeRepository;
use App\Repository\AdherentRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/signup/resend-code', name: 'api_signup_resend_code', methods: ['POST'])]
class SignupResendCodeController extends AbstractSignupController
{
    private const BACKOFF_WINDOW_HOURS = 24;
    private const BACKOFF_BASE_SECONDS = 30;
    private const BACKOFF_MAX_SECONDS = 3600;

    public function __construct(
        private readonly AdherentRepository $adherentRepository,
        private readonly AdherentActivationCodeRepository $activationCodeRepository,
        private readonly MessageBusInterface $bus,
        private readonly RateLimiterFactory $signupCodeAttemptLimiter,
    ) {
    }

    public function __invoke(
        Request $request,
        #[MapRequestPayload(serializationContext: ['groups' => ['signup:write']])]
        SignupResendCodeRequest $payload,
    ): Response {
        $this->enforceIpRateLimit($this->signupCodeAttemptLimiter, $request);

        $adherent = $this->adherentRepository->findOneByEmail((string) $payload->email);

        if ($adherent instanceof Adherent && $adherent->isPending() && !$this->isBackoffActive($adherent)) {
            $this->bus->dispatch(new SendSignupConfirmationCommand($adherent));
        }

        return new Response('', Response::HTTP_NO_CONTENT);
    }

    private function isBackoffActive(Adherent $adherent): bool
    {
        $recent = $this->activationCodeRepository->findRecentByAdherent(
            $adherent,
            new \DateTime(\sprintf('-%d hours', self::BACKOFF_WINDOW_HOURS)),
        );

        if ([] === $recent) {
            return false;
        }

        $policy = new ExponentialBackoffPolicy(self::BACKOFF_BASE_SECONDS, self::BACKOFF_MAX_SECONDS);

        return $policy->isThrottled(\count($recent), $recent[0]->getCreatedAt());
    }
}
