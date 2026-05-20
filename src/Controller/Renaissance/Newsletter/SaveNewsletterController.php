<?php

declare(strict_types=1);

namespace App\Controller\Renaissance\Newsletter;

use App\Recaptcha\FriendlyCaptchaV2ApiClient;
use App\Renaissance\Newsletter\NewsletterManager;
use App\Renaissance\Newsletter\SubscriptionRequest;
use App\Repository\Renaissance\NewsletterSourceRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class SaveNewsletterController extends AbstractController
{
    public function __construct(
        private readonly SerializerInterface $serializer,
        private readonly ValidatorInterface $validator,
        private readonly NewsletterManager $newsletterManager,
        private readonly NewsletterSourceRepository $newsletterSourceRepository,
        private readonly string $friendlyCaptchaNewsletterSiteKey,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        $subscription = $this->serializer->deserialize($request->getContent(), SubscriptionRequest::class, JsonEncoder::FORMAT, [
            AbstractNormalizer::GROUPS => ['newsletter:write'],
        ]);

        $subscription->setRecaptchaSiteKey($this->friendlyCaptchaNewsletterSiteKey);
        $subscription->setRecaptchaApi(FriendlyCaptchaV2ApiClient::NAME);

        $errors = $this->validator->validate($subscription);

        if ($errors->count()) {
            return $this->json($errors, Response::HTTP_BAD_REQUEST);
        }

        $source = $this->newsletterSourceRepository->findOneByCode($subscription->source);

        if (!$source || !$source->enabled) {
            $violations = new ConstraintViolationList();
            $violations->add(new ConstraintViolation(
                'Cette source d\'inscription n\'est pas autorisée.',
                null,
                [],
                $subscription,
                'source',
                $subscription->source
            ));

            return $this->json($violations, Response::HTTP_BAD_REQUEST);
        }

        $this->newsletterManager->saveSubscription($subscription);

        return $this->json('OK', Response::HTTP_CREATED);
    }
}
