<?php

namespace App\Controller\Api\NationalEvent;

use App\Entity\Adherent;
use App\Entity\NationalEvent\EventInscription;
use App\Entity\NationalEvent\TicketScan;
use App\Normalizer\ImageExposeNormalizer;
use App\Normalizer\TranslateAdherentTagNormalizer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ScanTicketController extends AbstractController
{
    public function __invoke(EntityManagerInterface $entityManager, NormalizerInterface $normalizer, ?EventInscription $inscription, #[CurrentUser] Adherent $adherent): Response
    {
        if (!$inscription) {
            return $this->json([
                'status' => [
                    'code' => 'unknown',
                    'label' => 'Inconnu',
                    'type' => 'error',
                    'message' => 'Ce billet n’existe pas. Intervention SO et sortie de l’évent.',
                ],
            ]);
        }

        $scanHistory = $inscription->getTicketScans();
        $lastScanDate = $inscription->lastTicketScannedAt;

        if (!$inscription->lastTicketScannedAt || $inscription->lastTicketScannedAt < new \DateTimeImmutable('-1 minute')) {
            $inscription->addTicketScan(new TicketScan($adherent, $inscription->status));
            $entityManager->flush();
        }

        if ($inscription->isApproved()) {
            return $this->json([
                'status' => [
                    'code' => 'valid',
                    'label' => 'Valide',
                    'type' => 'success',
                    'message' => 'Personne autorisée à entrer',
                ],
                'type' => [
                    'label' => $inscription->ticketBracelet,
                    'color' => $inscription->ticketBraceletColor,
                    'door' => $inscription->ticketCustomDetail,
                ],
                'alert' => $lastScanDate?->format('d/m/Y') === date('d/m/Y') ? 'Déjà scannée aujourd’hui' : null,
                'uuid' => $inscription->getUuid()->toString(),
                'user' => $normalizer->normalize($inscription, context: [
                    TranslateAdherentTagNormalizer::ENABLE_TAG_TRANSLATOR => true,
                    'groups' => ['event_inscription_scan', ImageExposeNormalizer::NORMALIZATION_GROUP],
                ]),
                'visit_day' => $inscription->getVisitDayConfig()['titre'] ?? $inscription->visitDay,
                'transport' => $inscription->getTransportConfig()['titre'] ?? $inscription->transport,
                'accommodation' => $inscription->getAccommodationConfig()['titre'] ?? $inscription->accommodation,
                'scan_history' => array_map(static fn (TicketScan $scan) => [
                    'date' => $scan->getCreatedAt()->format('Y-m-d H:i:s'),
                    'name' => $scan->scannedBy?->getFullName(),
                    'public_id' => $scan->scannedBy?->getPublicId(),
                ], $scanHistory),
            ]);
        }

        return $this->json([
            'status' => [
                'code' => 'invalid',
                'label' => 'Invalide',
                'type' => 'error',
                'message' => 'Ce billet n’existe pas. Intervention SO et sortie de l’évent.',
            ],
        ]);
    }
}
