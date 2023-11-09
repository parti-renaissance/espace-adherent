<?php

namespace App\Controller\Api\AdherentList;

use App\Entity\Geo\Zone;
use App\Geo\ManagedZoneProvider;
use App\Repository\AdherentRepository;
use App\Repository\Geo\ZoneRepository;
use App\Scope\ScopeGeneratorResolver;
use Ramsey\Uuid\Uuid;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Exception\NotEncodableValueException;
use Symfony\Component\Serializer\Exception\NotNormalizableValueException;
use Symfony\Component\Serializer\SerializerInterface;

#[Route(path: '/v3/adherents/count', name: 'app_adherents_count_get', methods: ['GET', 'POST'])]
#[Security("is_granted('ROLE_OAUTH_SCOPE_JEMENGAGE_ADMIN') and is_granted('IS_FEATURE_GRANTED', ['contacts', 'committee'])")]
class CountAdherentController extends AbstractController
{
    public function __construct(
        private readonly SerializerInterface $serializer,
        private readonly ZoneRepository $zoneRepository,
        private readonly AdherentRepository $adherentRepository,
        private readonly ScopeGeneratorResolver $resolver,
        private readonly ManagedZoneProvider $managedZoneProvider
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

        return $this->json([
            'adherent' => $this->adherentRepository->countInZones($zonesFromRequest ?: $scopeZones, true, false),
            'sympathizer' => $this->adherentRepository->countInZones($zonesFromRequest ?: $scopeZones, false, true),
        ]);
    }
}
