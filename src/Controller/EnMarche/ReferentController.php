<?php

namespace App\Controller\EnMarche;

use App\Entity\Adherent;
use App\Entity\ReferentOrganizationalChart\PersonOrganizationalChartItem;
use App\Form\ReferentPersonLinkType;
use App\FranceCities\FranceCities;
use App\Referent\OrganizationalChartManager;
use App\Repository\CommitteeRepository;
use App\Repository\ReferentOrganizationalChart\OrganizationalChartItemRepository;
use App\Repository\ReferentOrganizationalChart\ReferentPersonLinkRepository;
use App\Repository\ReferentRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * All the route names should start with 'app_referent_', if not you should modify App\EventListener\RecordReferentLastVisitListener.
 *
 * @Route("/espace-referent")
 */
class ReferentController extends AbstractController
{
    /**
     * @Route("/mon-equipe", name="app_referent_organizational_chart", methods={"GET"})
     * @Security("is_granted('ROLE_REFERENT') and is_granted('IS_ROOT_REFERENT')")
     */
    public function organizationalChartAction(
        OrganizationalChartItemRepository $organizationalChartItemRepository,
        ReferentRepository $referentRepository
    ): Response {
        return $this->render('referent/organizational_chart.html.twig', [
            'organization_chart_items' => $organizationalChartItemRepository->getRootNodes(),
            'referent' => $referentRepository->findOneByEmailAndSelectPersonOrgaChart($this->getUser()->getEmailAddress()),
        ]);
    }

    /**
     * @Route("/mon-equipe/{id}", name="app_referent_referent_person_link_edit", methods={"GET", "POST"})
     * @Security("is_granted('ROLE_REFERENT') and is_granted('IS_ROOT_REFERENT')")
     */
    public function editReferentPersonLink(
        Request $request,
        ReferentPersonLinkRepository $referentPersonLinkRepository,
        ReferentRepository $referentRepository,
        PersonOrganizationalChartItem $personOrganizationalChartItem,
        OrganizationalChartManager $manager
    ) {
        $personLink = $referentPersonLinkRepository->findOrCreateByOrgaItemAndReferent(
            $personOrganizationalChartItem,
            $referentRepository->findOneByEmail($this->getUser()->getEmailAddress())
        );

        if ($request->request->has('delete')) {
            $manager->delete($personLink);

            $this->addFlash('info', 'Organigramme mis à jour.');

            return $this->redirectToRoute('app_referent_organizational_chart');
        }

        $form = $this
            ->createForm(ReferentPersonLinkType::class, $personLink)
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            $manager->save($personLink);

            $this->addFlash('info', 'Organigramme mis à jour.');

            return $this->redirectToRoute('app_referent_organizational_chart');
        }

        return $this->render('referent/edit_referent_person_link.html.twig', [
            'form' => $form->createView(),
            'person_organizational_chart_item' => $personOrganizationalChartItem,
        ]);
    }

    /**
     * @Route("/mon-equipe/autocompletion/comite",
     *     name="app_referent_referent_person_link_autocomplete_committee",
     *     condition="request.isXmlHttpRequest()",
     *     methods={"GET"}
     * )
     * @Security("is_granted('ROLE_REFERENT') and is_granted('IS_ROOT_REFERENT')")
     */
    public function committeeAutocompleteAction(Request $request, CommitteeRepository $committeeRepository)
    {
        if (!$term = $request->query->get('term')) {
            return new JsonResponse([], Response::HTTP_BAD_REQUEST);
        }

        /** @var Adherent $referent */
        $referent = $this->getUser();

        $committees = $committeeRepository->findByPartialNameForReferent($referent, $term);

        foreach ($committees as $committee) {
            $result[] = [
                'uuid' => $committee->getUuid()->toString(),
                'name' => $committee->getName(),
            ];
        }

        return new JsonResponse($result ?? []);
    }

    /**
     * @Route("/mon-equipe/autocompletion/ville",
     *     name="app_referent_referent_person_link_autocomplete_city",
     *     condition="request.isXmlHttpRequest()",
     *     methods={"GET"}
     * )
     * @Security("is_granted('ROLE_REFERENT') and is_granted('IS_ROOT_REFERENT')")
     */
    public function cityAutocompleteAction(Request $request, FranceCities $franceCities): JsonResponse
    {
        if (!$search = $request->query->get('search')) {
            return new JsonResponse([], Response::HTTP_BAD_REQUEST);
        }

        /** @var Adherent $referent */
        $referent = $this->getUser();

        $managedZones = $referent->getManagedArea()->getZones();

        $foundedCities = $franceCities->searchCitiesForZones($managedZones->toArray(), $search);

        $result = [];
        foreach ($foundedCities as $city) {
            $result[] = ['name' => $city->getName(), 'insee_code' => $city->getInseeCode(), 'postal_code' => $city->getPostalCodeAsString()];
        }

        return new JsonResponse($result);
    }
}
