<?php

namespace App\Twig;

use App\Adherent\SessionModal\SessionModalActivatorListener;
use App\Adherent\Tag\TagTranslator;
use App\Entity\Adherent;
use App\Entity\Committee;
use App\Entity\ElectedRepresentative\ElectedRepresentative;
use App\Repository\AdherentMandate\CommitteeAdherentMandateRepository;
use App\Repository\AdherentRepository;
use App\Repository\CommitteeRepository;
use App\Repository\DonationRepository;
use App\Repository\ElectedRepresentative\ElectedRepresentativeRepository;
use Symfony\Component\HttpFoundation\Request;
use Twig\Extension\RuntimeExtensionInterface;

class AdherentRuntime implements RuntimeExtensionInterface
{
    public function __construct(
        private readonly ElectedRepresentativeRepository $electedRepresentativeRepository,
        private readonly CommitteeAdherentMandateRepository $committeeMandateRepository,
        private readonly AdherentRepository $adherentRepository,
        private readonly CommitteeRepository $committeeRepository,
        private readonly DonationRepository $donationRepository,
        private readonly TagTranslator $tagTranslator,
    ) {
    }

    public function translateTag(string $tag, bool $fullTag = true): string
    {
        return $this->tagTranslator->trans($tag, $fullTag);
    }

    public function getAdherentRoleLabels(Adherent $adherent): array
    {
        $labels = [];

        if ($adherent->isRenaissanceAdherent()) {
            $labels[] = $adherent->isFemale() ? 'Adhérente' : 'Adhérent';
        } elseif ($adherent->isRenaissanceSympathizer()) {
            $labels[] = $adherent->isFemale() ? 'Sympathisante' : 'Sympathisant';
        } else {
            $labels[] = 'Non-adhérent(e)';
        }

        if ($adherent->isDeputy()) {
            $labels[] = $adherent->isFemale() ? 'Déléguée de circonscription' : 'Délégué de circonscription';
        }

        if ($adherent->isDelegatedDeputy()) {
            $labels[] = 'Équipe du délégué de circonscription';
        }

        if ($adherent->isSupervisor()) {
            $labels[] = $adherent->isFemale() ? 'Animatrice' : 'Animateur';
        }

        if ($adherent->isHost()) {
            $labels[] = $adherent->isFemale() ? 'Co-animatrice' : 'Co-animateur';
        }

        if ($this->committeeMandateRepository->hasActiveMandate($adherent)) {
            $labels[] = $adherent->isFemale() ? 'Adhérente désignée' : 'Adhérent désigné';
        }

        return $labels;
    }

    public function getElectedRepresentative(Adherent $adherent): ?ElectedRepresentative
    {
        return $this->electedRepresentativeRepository->findOneBy(['adherent' => $adherent]);
    }

    public function hasActiveParliamentaryMandate(Adherent $adherent): bool
    {
        return $this->electedRepresentativeRepository->hasActiveParliamentaryMandate($adherent);
    }

    public function getSessionModalContext(Request $request): ?string
    {
        if (!$modalContext = $request->getSession()->get(SessionModalActivatorListener::SESSION_KEY)) {
            return null;
        }

        if (SessionModalActivatorListener::CONTEXT_READHESION === $modalContext) {
            if (str_contains($request->getPathInfo(), 'espace-adherent')) {
                return $request->getSession()->remove(SessionModalActivatorListener::SESSION_KEY);
            }

            return null;
        }

        return $request->getSession()->remove(SessionModalActivatorListener::SESSION_KEY);
    }

    public function countContribution(Adherent $adherent, \DateTime $before): int
    {
        return $this->donationRepository->countCotisationForAdherent($adherent, $before);
    }

    public function getAdherentByUuid(string $uuid): ?Adherent
    {
        return $this->adherentRepository->findOneByUuid($uuid);
    }

    public function getCommitteeByUuid(string $uuid): ?Committee
    {
        return $this->committeeRepository->findOneByUuid($uuid);
    }
}
