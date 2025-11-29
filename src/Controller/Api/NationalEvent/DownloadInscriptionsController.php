<?php

declare(strict_types=1);

namespace App\Controller\Api\NationalEvent;

use App\Adherent\Tag\TagTranslator;
use App\Entity\NationalEvent\EventInscription;
use App\Repository\NationalEvent\EventInscriptionRepository;
use App\Scope\ScopeGeneratorResolver;
use App\Utils\PhoneNumberUtils;
use Sonata\Exporter\ExporterInterface;
use Sonata\Exporter\Source\IteratorCallbackSourceIterator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted(new Expression("is_granted('REQUEST_SCOPE_GRANTED', 'rentree')"))]
#[Route(path: '/v3/national_event_inscriptions.xlsx', name: 'api_national_event_inscriptions_get_inscriptions', methods: ['GET'])]
class DownloadInscriptionsController extends AbstractController
{
    public function __construct(
        private readonly TagTranslator $tagTranslator,
        private readonly ScopeGeneratorResolver $scopeGeneratorResolver,
        private readonly EventInscriptionRepository $repository,
        private readonly ExporterInterface $exporter,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        if (!$scope = $this->scopeGeneratorResolver->generate()) {
            throw $this->createAccessDeniedException('Accès refusé.');
        }

        $inscriptionTypeFilter = $request->query->all('exists')['adherent'] ?? null;

        $data = $this->repository->findAllForCurrentCampus(
            $scope->getZones(),
            $scope->getCommitteeUuids(),
            null !== $inscriptionTypeFilter ? 'true' === $inscriptionTypeFilter : null,
            trim($request->query->get('search', '')),
        );

        return $this->exporter->getResponse(
            'xlsx',
            \sprintf('Inscriptions - %s.xlsx', date('d-m-Y H:i')),
            new IteratorCallbackSourceIterator(
                new \ArrayIterator($data),
                fn (EventInscription $eventInscription) => [
                    'Numéro militant' => $eventInscription->adherent?->getPublicId(),
                    'Civilité' => $eventInscription->getCivility()->value,
                    'Prénom' => $eventInscription->firstName,
                    'Nom' => $eventInscription->lastName,
                    'Email' => $eventInscription->addressEmail,
                    'Téléphone' => PhoneNumberUtils::format($eventInscription->phone),
                    'Code postal' => (string) $eventInscription->postalCode,
                    'Labels militants' => implode(', ', array_map([$this->tagTranslator, 'trans'], $eventInscription->getMemberTags())),
                    'Labels élu' => implode(', ', array_map([$this->tagTranslator, 'trans'], $eventInscription->getElectTags() ?? [])),
                    'Labels divers' => implode(', ', array_map([$this->tagTranslator, 'trans'], $eventInscription->getOtherTags() ?? [])),
                    'Jour de présence' => $eventInscription->getVisitDayConfig()['titre'] ?? null,
                    'Forfait transport' => $eventInscription->getTransportConfig()['titre'] ?? null,
                    'Forfait hébergement' => $eventInscription->getAccommodationConfig()['titre'] ?? null,
                    'Partenaire de chambre' => $eventInscription->roommateIdentifier,
                    'Est JAM' => $eventInscription->isJAM ? 'Oui' : 'Non',
                    'Handicap' => $eventInscription->accessibility,
                    'Montant' => (string) $eventInscription->getAmountInEuro(),
                    'Statut du paiement' => $eventInscription->paymentStatus->value,
                    'Date d\'inscription' => $eventInscription->getCreatedAt()->format('d/m/Y H:i:s'),
                    'Date de dernière modification' => $eventInscription->getUpdatedAt()->format('d/m/Y H:i:s'),
                    'Date de confirmation' => $eventInscription->confirmedAt?->format('d/m/Y H:i:s'),
                ]
            )
        );
    }
}
