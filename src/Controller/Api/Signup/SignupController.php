<?php

declare(strict_types=1);

namespace App\Controller\Api\Signup;

use App\Address\Address;
use App\Membership\Signup\SignupCommand;
use App\Membership\Signup\SignupHandler;
use App\Recaptcha\FriendlyCaptchaV2ApiClient;
use App\Repository\SignupSourceRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
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
class SignupController extends AbstractController
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
        if (!$this->signupLimiter->create($request->getClientIp() ?? 'unknown')->consume()->isAccepted()) {
            throw new TooManyRequestsHttpException();
        }

        try {
            /** @var SignupRequest $signupRequest */
            $signupRequest = $this->serializer->deserialize($request->getContent(), SignupRequest::class, JsonEncoder::FORMAT, [
                AbstractNormalizer::GROUPS => ['signup:write'],
            ]);
        } catch (SerializerExceptionInterface) {
            return $this->json(['error' => 'Invalid request payload.'], Response::HTTP_BAD_REQUEST);
        }

        $source = $signupRequest->source
            ? $this->signupSourceRepository->findOneByCode($signupRequest->source)
            : null;

        $signupRequest->setRecaptchaSiteKey($source?->friendlyCaptchaSiteKey ?: $this->friendlyCaptchaNewsletterSiteKey);
        $signupRequest->setRecaptchaApi(FriendlyCaptchaV2ApiClient::NAME);

        $errors = $this->validator->validate($signupRequest);

        if ($errors->count()) {
            return $this->json($errors, Response::HTTP_BAD_REQUEST);
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

        $this->signupHandler->register(new SignupCommand(
            email: $signupRequest->email,
            source: $source->code,
            firstName: $signupRequest->firstName,
            lastName: $signupRequest->lastName,
            phone: $signupRequest->phone,
            gender: $signupRequest->civility,
            address: $this->buildAddress($signupRequest),
            emailOptIn: $signupRequest->emailOptIn,
            smsOptIn: $signupRequest->smsOptIn,
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
