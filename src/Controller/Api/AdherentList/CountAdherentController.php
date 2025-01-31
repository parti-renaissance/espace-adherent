<?php

namespace App\Controller\Api\AdherentList;

use App\Entity\Geo\Zone;
use App\Geo\ManagedZoneProvider;
use App\Repository\AdherentRepository;
use App\Repository\Geo\ZoneRepository;
use App\Scope\ScopeGeneratorResolver;
use Ramsey\Uuid\Uuid;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Exception\NotEncodableValueException;
use Symfony\Component\Serializer\Exception\NotNormalizableValueException;
use Symfony\Component\Serializer\SerializerInterface;

#[IsGranted(new Expression("is_granted('REQUEST_SCOPE_GRANTED', ['contacts', 'committee', 'designation'])"))]
#[Route(path: '/v3/adherents/count', name: 'app_adherents_count_get', methods: ['GET', 'POST'])]
class CountAdherentController extends AbstractController
{
    public function __construct(
        private readonly SerializerInterface $serializer,
        private readonly ZoneRepository $zoneRepository,
        private readonly AdherentRepository $adherentRepository,
        private readonly ScopeGeneratorResolver $resolver,
        private readonly ManagedZoneProvider $managedZoneProvider,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        $scope = $this->resolver->generate();
        $scopeZones = $scope->getZones();
        $zonesFromRequest = [];

        if ($request->isMethod('POST')) {
            try {
                $zoneUuids = $this->serializer->deserialize(
                    $request->getContent(),
                    Uuid::class.'[]',
                    JsonEncoder::FORMAT
                );
            } catch (NotNormalizableValueException $e) {
                throw new BadRequestHttpException('Invalid UUID value');
            } catch (NotEncodableValueException $e) {
                $zoneUuids = [];
            }

            $scopeZonesIds = array_map(fn (Zone $zone) => $zone->getId(), $scopeZones);

            $zonesFromRequest = array_filter(
                $this->zoneRepository->findBy(['uuid' => $zoneUuids]),
                fn (Zone $zone) => $this->managedZoneProvider->zoneBelongsToSome($zone, $scopeZonesIds)
            );
        }

        $data = [
            'adherent' => $this->adherentRepository->countInZones($zonesFromRequest ?: $scopeZones, true, false),
            'sympathizer' => $this->adherentRepository->countInZones($zonesFromRequest ?: $scopeZones, false, true),
        ];

        if ($since = (int) $request->query->get('since')) {
            $data['adherent_since'] = $this->adherentRepository->countInZones($zonesFromRequest ?: $scopeZones, true, false, $since);
        }

        return $this->json($data);
    }
}
