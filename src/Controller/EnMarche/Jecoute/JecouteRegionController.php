<?php

namespace App\Controller\EnMarche\Jecoute;

use App\Controller\EnMarche\AccessDelegatorTrait;
use App\Entity\Jecoute\Region;
use App\Form\Jecoute\RegionType;
use App\Jecoute\RegionManager;
use App\Repository\Geo\RegionRepository as GeoRegionRepository;
use App\Repository\Jecoute\RegionRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Ramsey\Uuid\Uuid;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/espace-candidat/campagne", name="app_jecoute_candidate_region_")
 *
 * @Security("is_granted('ROLE_JECOUTE_REGION') or (is_granted('ROLE_DELEGATED_CANDIDATE') and is_granted('HAS_DELEGATED_ACCESS_JECOUTE_REGION'))")
 */
class JecouteRegionController extends AbstractController
{
    use AccessDelegatorTrait;

    /**
     * @Route(path="/editer", name="edit", methods={"GET|POST"})
     */
    public function editJecouteRegion(
        Request $request,
        ObjectManager $manager,
        GeoRegionRepository $geoRegionRepository,
        RegionRepository $regionRepository,
        RegionManager $regionManager
    ): Response {
        $adherent = $this->getMainUser($request->getSession());
        $zone = $adherent->getCandidateManagedArea()->getZone();
        if (!$zone->isRegion()) {
            throw $this->createNotFoundException('Managed area is not region.');
        }

        $geoRegion = $geoRegionRepository->findOneBy(['code' => $zone->getCode()]);
        $region = $regionRepository->findOneBy(['geoRegion' => $geoRegion]);
        $isNew = false;
        if (!$region) {
            $region = new Region(Uuid::uuid4(), $geoRegion);
            $isNew = true;
        }

        $form = $this
            ->createForm(RegionType::class, $region)
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            $regionManager->handleFile($region);
            $manager->persist($form->getData());
            $manager->flush();

            $this->addFlash('info', $isNew ? 'jecoute_region.create.success' : 'jecoute_region.edit.success');
            $isNew = false;
        }

        return $this->render('jecoute/edit_region.html.twig', [
            'form' => $form->createView(),
            'is_creation' => $isNew,
        ]);
    }
}
