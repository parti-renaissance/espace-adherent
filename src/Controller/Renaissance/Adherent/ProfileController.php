<?php

declare(strict_types=1);

namespace App\Controller\Renaissance\Adherent;

use App\AdherentProfile\AdherentProfile;
use App\AdherentProfile\AdherentProfileHandler;
use App\Analytics\PostHog\Events\PostHogEventName;
use App\Analytics\PostHog\PostHogService;
use App\Entity\Adherent;
use App\Form\AdherentProfileType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Service\Attribute\Required;

#[Route(path: '/parametres/mon-compte', name: 'app_renaissance_adherent_profile', methods: ['GET', 'POST'])]
class ProfileController extends AbstractController
{
    // Fields tracked for changed detection — noms techniques uniquement, jamais les valeurs (CRITIQUE PII).
    private const TRACKED_FIELDS = [
        'gender', 'customGender', 'firstName', 'lastName', 'birthdate',
        'position', 'phone', 'emailAddress', 'nationality',
        'facebookPageUrl', 'twitterPageUrl', 'linkedinPageUrl',
        'telegramPageUrl', 'instagramPageUrl', 'tiktokPageUrl',
        'job', 'activityArea', 'mandates', 'interests',
        'subscriptionTypes', 'partyMembership',
    ];

    private PostHogService $postHog;

    #[Required]
    public function setPostHogService(PostHogService $postHog): void
    {
        $this->postHog = $postHog;
    }

    public function __invoke(Request $request, AdherentProfileHandler $handler): Response
    {
        /** @var Adherent $adherent */
        $adherent = $this->getUser();

        $adherentProfile = AdherentProfile::createFromAdherent($adherent);

        // Snapshot AVANT handleRequest pour détecter les champs modifiés (noms techniques, pas les valeurs).
        $originalProfile = clone $adherentProfile;

        $form = $this
            ->createForm(AdherentProfileType::class, $adherentProfile, [
                'disabled_form' => $adherent->isCertified(),
                'is_renaissance' => true,
            ])
            ->handleRequest($request)
        ;

        if ($request->isMethod('GET')) {
            $this->postHog->captureServerSide(
                PostHogEventName::PROFILE_PAGE_VIEWED,
                [],
                $adherent,
            );
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $handler->update($adherent, $adherentProfile);
            $this->addFlash('info', 'adherent.update_profile.success');

            $this->postHog->captureServerSide(
                PostHogEventName::PROFILE_UPDATED,
                [
                    // Noms de champs techniques modifiés — JAMAIS les valeurs (CRITIQUE PII).
                    'fields_changed' => $this->detectChangedFields($originalProfile, $adherentProfile),
                ],
                $adherent,
            );

            return $this->redirectToRoute('app_renaissance_adherent_profile');
        }

        return $this->render('renaissance/adherent/profile/form.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * Retourne la liste des noms de champs modifiés entre le snapshot original et le DTO soumis.
     * JAMAIS les valeurs — noms techniques uniquement (CRITIQUE PII).
     *
     * Note Phase 1.5 Fontaine : refactoriser en helper AdherentProfile::diffFieldNames(AdherentProfile $a, AdherentProfile $b)
     * pour couvrir les champs composites (postAddress → address/postal_code/city/country distincts).
     *
     * @return list<string>
     */
    private function detectChangedFields(AdherentProfile $original, AdherentProfile $submitted): array
    {
        $changed = [];

        foreach (self::TRACKED_FIELDS as $field) {
            $getter = 'get'.ucfirst($field);

            if (!method_exists($original, $getter)) {
                // Propriété publique (partyMembership, mandates, interests, subscriptionTypes)
                if ($original->$field !== $submitted->$field) {
                    $changed[] = $field;
                }
                continue;
            }

            $originalValue = $original->$getter();
            $submittedValue = $submitted->$getter();

            if ($originalValue !== $submittedValue) {
                $changed[] = $field;
            }
        }

        // postAddress traité séparément (objet composite) : on compare la sérialisation.
        // Fontaine Phase 1.5 : décomposer en sous-champs (address, postal_code, city, country).
        if (serialize($original->getPostAddress()) !== serialize($submitted->getPostAddress())) {
            $changed[] = 'post_address';
        }

        return $changed;
    }
}
