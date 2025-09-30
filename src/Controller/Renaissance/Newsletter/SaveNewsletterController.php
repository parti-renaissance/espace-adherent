<?php

namespace App\Controller\Renaissance\Newsletter;

use App\Newsletter\NewsletterTypeEnum;
use App\Renaissance\Newsletter\NewsletterManager;
use App\Renaissance\Newsletter\SubscriptionRequest;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route(path: '/api/newsletter', name: 'app_renaissance_newsletter_save', methods: ['POST'])]
class SaveNewsletterController extends AbstractController
{
    public function __construct(
        private readonly SerializerInterface $serializer,
        private readonly ValidatorInterface $validator,
        private readonly NewsletterManager $newsletterManager,
        private readonly string $friendlyCaptchaEuropeSiteKey,
        private readonly string $friendlyCaptchaNRPSiteKey,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        $subscription = $this->serializer->deserialize($request->getContent(), SubscriptionRequest::class, JsonEncoder::FORMAT, [
            AbstractNormalizer::GROUPS => ['newsletter:write'],
        ]);

        $subscription->setRecaptchaSiteKey(match ($subscription->source) {
            NewsletterTypeEnum::SITE_EU => $this->friendlyCaptchaEuropeSiteKey,
            NewsletterTypeEnum::SITE_NRP => $this->friendlyCaptchaNRPSiteKey,
            default => null,
        });

        $errors = $this->validator->validate($subscription);

        if ($errors->count()) {
            return $this->json($errors, Response::HTTP_BAD_REQUEST);
        }

        $this->newsletterManager->saveSubscription($subscription);

        return $this->json('OK', Response::HTTP_CREATED);
    }
}
