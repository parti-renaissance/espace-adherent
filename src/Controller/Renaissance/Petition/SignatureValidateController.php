<?php

declare(strict_types=1);

namespace App\Controller\Renaissance\Petition;

use App\Analytics\PostHog\Events\PostHogEventName;
use App\Analytics\PostHog\HashEmailService;
use App\Analytics\PostHog\PostHogService;
use App\Analytics\PostHog\SiteContext;
use App\Entity\PetitionSignature;
use App\Renaissance\Petition\SignatureManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Service\Attribute\Required;

#[Route(path: '/petition/validate/{uuid}/{token}', name: 'app_petition_validate', methods: ['GET'])]
class SignatureValidateController extends AbstractController
{
    private PostHogService $postHog;
    private HashEmailService $hashEmail;
    private SiteContext $siteContext;

    public function __construct(private readonly SignatureManager $signatureManager)
    {
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

    #[Required]
    public function setSiteContext(SiteContext $siteContext): void
    {
        $this->siteContext = $siteContext;
    }

    public function __invoke(PetitionSignature $signature, string $token): Response
    {
        $thanksUrl = \sprintf(
            '%s/petitions/%s?merci',
            rtrim($this->generateUrl('renaissance_site'), '/'),
            $signature->petitionSlug
        );

        if ($signature->validatedAt) {
            return $this->redirect($thanksUrl);
        }

        try {
            $this->signatureManager->validate($signature, $token);
        } catch (\InvalidArgumentException) {
            $this->addFlash('error', 'Le lien de confirmation est invalide');

            return $this->render('renaissance/petition/confirmation_error.html.twig');
        }

        if ($this->siteContext->isInitialized()) {
            $this->postHog->captureServerSideWithSet(
                PostHogEventName::PETITION_SIGNED_SERVER,
                ['petition_slug' => $signature->petitionSlug],
                ['email' => $signature->emailAddress],
                $this->hashEmail->hash($signature->emailAddress),
            );
        }

        return $this->redirect($thanksUrl);
    }
}
