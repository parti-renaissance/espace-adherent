<?php

declare(strict_types=1);

namespace App\Controller\Api\Signup;

use App\Address\Address;
use App\Membership\Signup\Request\SignupRequest;
use App\Membership\Signup\SignupCommand;
use App\Membership\Signup\SignupHandler;
use App\Recaptcha\FriendlyCaptchaV2ApiClient;
use App\Repository\SignupSourceRepository;
use App\Validator\StrictEmail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Exception\ExceptionInterface as SerializerExceptionInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route(path: '/signup', name: 'api_signup', methods: ['POST'])]
class SignupController extends AbstractSignupController
{
    public function __construct(
        private readonly SerializerInterface $serializer,
        private readonly ValidatorInterface $validator,
        private readonly SignupHandler $signupHandler,
        private readonly SignupSourceRepository $signupSourceRepository,
        private readonly RateLimiterFactory $signupLimiter,
        private readonly string $friendlyCaptchaNewsletterSiteKey,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        $this->enforceIpRateLimit($this->signupLimiter, $request);

        try {
            /** @var SignupRequest $signupRequest */
            $signupRequest = $this->serializer->deserialize($request->getContent(), SignupRequest::class, JsonEncoder::FORMAT, [
                AbstractNormalizer::GROUPS => ['signup:write'],
            ]);
        } catch (SerializerExceptionInterface $exception) {
            throw new BadRequestHttpException('Invalid request payload.', $exception);
        }

        $source = $signupRequest->source
            ? $this->signupSourceRepository->findOneByCode($signupRequest->source)
            : null;

        $signupRequest->setRecaptchaSiteKey($source?->friendlyCaptchaSiteKey ?: $this->friendlyCaptchaNewsletterSiteKey);
        $signupRequest->setRecaptchaApi(FriendlyCaptchaV2ApiClient::NAME);

        $violations = $this->validator->validate($signupRequest);

        $blockingViolations = new ConstraintViolationList();
        foreach ($violations as $violation) {
            $isSoftEmailWarning = $violation->getConstraint() instanceof StrictEmail
                && StrictEmail::LEVEL_WARNING === $violation->getCause();
            if (!$isSoftEmailWarning) {
                $blockingViolations->add($violation);
            }
        }

        if ($blockingViolations->count()) {
            return $this->json($blockingViolations, Response::HTTP_BAD_REQUEST);
        }

        if (!$source || !$source->enabled) {
            $violations = new ConstraintViolationList();
            $violations->add(new ConstraintViolation(
                'Cette source d\'inscription n\'est pas autorisée.',
                null,
                [],
                $signupRequest,
                'source',
                $signupRequest->source
            ));

            return $this->json($violations, Response::HTTP_BAD_REQUEST);
        }

        $this->signupHandler->handle(new SignupCommand(
            email: $signupRequest->email,
            source: $source->code,
            firstName: $signupRequest->firstName,
            lastName: $signupRequest->lastName,
            phone: $signupRequest->phone,
            gender: $signupRequest->civility,
            address: $this->buildAddress($signupRequest),
            emailOptIn: $signupRequest->emailOptIn,
            smsOptIn: $signupRequest->smsOptIn,
            utmSource: $signupRequest->utmSource,
            utmCampaign: $signupRequest->utmCampaign,
            referrerCode: $signupRequest->referrerCode,
        ));

        return $this->json(null, Response::HTTP_CREATED);
    }

    private function buildAddress(SignupRequest $request): ?Address
    {
        if (!$request->postalCode && !$request->cityName && !$request->country && !$request->address) {
            return null;
        }

        $address = new Address();
        $address->setCountry($request->country ?? 'FR');
        $address->setPostalCode($request->postalCode);
        $address->setCityName($request->cityName);
        $address->setAddress($request->address);

        return $address;
    }
}
