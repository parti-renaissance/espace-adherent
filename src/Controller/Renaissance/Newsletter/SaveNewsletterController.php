<?php

declare(strict_types=1);

namespace App\Controller\Renaissance\Newsletter;

use App\Analytics\PostHog\Events\PostHogEventName;
use App\Analytics\PostHog\HashEmailService;
use App\Analytics\PostHog\PostHogService;
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
use Symfony\Contracts\Service\Attribute\Required;

class SaveNewsletterController extends AbstractController
{
    private PostHogService $postHog;
    private HashEmailService $hashEmail;

    public function __construct(
        private readonly SerializerInterface $serializer,
        private readonly ValidatorInterface $validator,
        private readonly NewsletterManager $newsletterManager,
        private readonly NewsletterSourceRepository $newsletterSourceRepository,
        private readonly string $friendlyCaptchaNewsletterSiteKey,
    ) {
    }

    #[Required]
    public function setPostHogService(PostHogService $postHog): void
    {
        $this->postHog = $postHog;
    }

    #[Required]
    public function setHashEmailService(HashEmailService $hashEmail): void
    {
        $this->hashEmail = $hashEmail;
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

        $this->postHog->captureServerSideWithSet(
            PostHogEventName::NEWSLETTER_SUBMITTED_SERVER,
            [
                'postal_code_prefix' => substr($subscription->postalCode ?? '', 0, 2),
                'source_page' => $request->headers->get('Referer', ''),
            ],
            ['email' => $subscription->email],
            $this->hashEmail->hash($subscription->email),
        );

        return $this->json('OK', Response::HTTP_CREATED);
    }
}
