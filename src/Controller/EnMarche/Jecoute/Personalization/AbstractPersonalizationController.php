<?php

declare(strict_types=1);

namespace App\Controller\EnMarche\Jecoute\Personalization;

use App\Controller\EnMarche\AccessDelegatorTrait;
use App\Entity\Adherent;
use App\Entity\Geo\Zone;
use App\Entity\Jecoute\Region;
use App\Form\Jecoute\RegionType;
use App\Jecoute\RegionManager;
use App\Repository\Geo\ZoneRepository;
use App\Repository\Jecoute\RegionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Ramsey\Uuid\Uuid;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

abstract class AbstractPersonalizationController extends AbstractController
{
    use AccessDelegatorTrait;

    protected $regionRepository;
    protected $zoneRepository;

    public function __construct(RegionRepository $regionRepository, ZoneRepository $zoneRepository)
    {
        $this->regionRepository = $regionRepository;
        $this->zoneRepository = $zoneRepository;
    }

    #[Route(path: '/editer', name: 'edit', methods: ['GET|POST'])]
    public function editJecoutePersonalization(
        Request $request,
        EntityManagerInterface $manager,
        RegionManager $regionManager,
    ): Response {
        $adherent = $this->getMainUser($request->getSession());
        $zones = $this->getZones($adherent);
        $zoneId = $request->query->get('zone_id', null);
        $personalization = null;
        $isNew = false;

        if (!$zoneId) {
            $personalization = $this->regionRepository->findOneBy(['zone' => $zones[0]]);
            if (!$personalization) {
                $personalization = $this->createPersonnalization($zones[0], $adherent);
                $isNew = true;
            }
        }

        if ($zoneId) {
            /** @var Zone $zone */
            $zone = $this->zoneRepository->find($zoneId);
            $personalization = $this->regionRepository->findOneBy(['zone' => $zone]);

            if (!$personalization) {
                $personalization = $this->createPersonnalization($zone, $adherent);
                $isNew = true;
            }
        }

        $options = ['zones' => $zones];
        if (\count($zones) > 1) {
            $options['has_multi_zone'] = true;
        }

        $form = $this
            ->createForm(RegionType::class, $personalization, $options)
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            $regionManager->handleFile($personalization);
            $manager->persist($form->getData());
            $manager->flush();

            $this->addFlash('info', $isNew ? 'jecoute_region.create.success' : 'jecoute_region.edit.success');

            return $this->redirectToRegionRoute('edit', ['zone_id' => $zoneId]);
        }

        return $this->renderTemplate('jecoute/edit_region.html.twig', [
            'form' => $form->createView(),
            'is_creation' => $isNew,
            'region' => $personalization,
        ]);
    }

    abstract protected function getSpaceName(): string;

    abstract protected function getZones(Adherent $adherent): array;

    protected function renderTemplate(string $template, array $parameters = []): Response
    {
        return $this->render($template, array_merge(
            $parameters,
            [
                'base_template' => \sprintf('jecoute/_base_%s_space.html.twig', $spaceName = $this->getSpaceName()),
                'space_name' => $spaceName,
            ]
        ));
    }

    protected function redirectToRegionRoute(string $subName, array $parameters = []): Response
    {
        return $this->redirectToRoute("app_jecoute_{$this->getSpaceName()}_region_{$subName}", $parameters);
    }

    private function createPersonnalization(Zone $zone, Adherent $adherent): Region
    {
        $personalization = new Region(Uuid::uuid4(), $zone);
        $personalization->setAuthor($adherent);

        return $personalization;
    }
}
