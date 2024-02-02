<?php

namespace App\Controller\EnMarche\MyTeam;

use App\Entity\Adherent;
use App\Entity\MyTeam\DelegatedAccess;
use App\Form\MyTeam\DelegateAccessType;
use App\Form\MyTeam\MyTeamSearchAdherentType;
use App\FranceCities\FranceCities;
use App\Repository\AdherentRepository;
use App\Repository\CommitteeRepository;
use App\Repository\Geo\ZoneRepository;
use App\Repository\MyTeam\DelegatedAccessRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

abstract class AbstractMyTeamController extends AbstractController
{
    #[Route(path: '', name: 'list')]
    public function list(DelegatedAccessRepository $delegatedAccessRepository): Response
    {
        $delegatedAccesses = $delegatedAccessRepository->findBy(['delegator' => $this->getUser(), 'type' => $this->getSpaceType()]);

        return $this->renderTemplate('my_team/list.html.twig', [
            'delegatedAccesses' => $delegatedAccesses,
        ]);
    }

    #[Route(path: '/deleguer-acces', name: 'delegate_access', methods: ['GET', 'POST'])]
    #[Route(path: '/deleguer-acces/{uuid}', name: 'delegate_access_edit', methods: ['GET', 'POST'])]
    #[Security('delegatedAccess ? delegatedAccess.getDelegator() == user : true')]
    public function delegateAccess(
        Request $request,
        EntityManagerInterface $entityManager,
        ?DelegatedAccess $delegatedAccess = null
    ): Response {
        $delegatedAccess ??= new DelegatedAccess();
        $delegatedAccess->setDelegator($this->getUser());
        $delegatedAccess->setType($this->getSpaceType());

        $filterForm = $this->createForm(MyTeamSearchAdherentType::class);

        $form = $this->createForm(DelegateAccessType::class, $delegatedAccess, ['type' => $this->getSpaceType()]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $new = null === $delegatedAccess->getId();
            $entityManager->persist($delegatedAccess);
            $entityManager->flush();

            $this->addFlash('info', $new ? 'delegated_access.created_successfully' : 'delegated_access.updated_successfully');

            return $this->redirectToRoute(sprintf('app_%s_my_team_list', $this->getSpaceType()));
        }

        return $this->renderTemplate('my_team/delegate_access.html.twig', [
            'filter_form' => $filterForm->createView(),
            'form' => $form->createView(),
        ]);
    }

    #[Route(path: '/deleguer-acces/{uuid}/supprimer', name: 'delegate_access_delete', methods: ['GET'])]
    #[Security('delegatedAccess.getDelegator() == user')]
    public function deleteDelegatedAccess(DelegatedAccess $delegatedAccess, EntityManagerInterface $entityManager)
    {
        $entityManager->remove($delegatedAccess);
        $entityManager->flush();

        $this->addFlash('info', 'delegated_access.deleted_successfully');

        return $this->redirectToRoute(sprintf('app_%s_my_team_list', $this->getSpaceType()));
    }

    #[Route(path: '/search', name: 'search', methods: ['POST'])]
    public function search(Request $request, AdherentRepository $adherentRepository): Response
    {
        $form = $this->createForm(MyTeamSearchAdherentType::class);
        $form->handleRequest($request);

        $users = [];
        if ($form->isSubmitted() && $form->isValid()) {
            // refTags migration: keep only 'else' logic after the migration is completed
            if ($this->getManagedTags($this->getUser())) {
                $users = $adherentRepository->findAdherentsByNameAndReferentTags($this->getManagedTags($this->getUser()), $form->get('name')->getData());
            } else {
                $users = $adherentRepository->findAdherentsByName($this->getZones($this->getUser()), $form->get('name')->getData());
            }
        }

        if (empty($users)) {
            return $this->json(['result' => false, 'content' => null]);
        }

        return $this->json([
            'result' => true,
            'content' => $this->renderView('my_team/search_results.html.twig', [
                'form' => $form->createView(),
                'users' => $users,
            ]),
        ]);
    }

    #[Route(path: '/mon-equipe/autocompletion/comite', name: 'autocomplete_committee', condition: 'request.isXmlHttpRequest()', methods: ['GET'])]
    public function committeeAutocompleteAction(Request $request, CommitteeRepository $committeeRepository)
    {
        if (!$term = $request->query->get('term')) {
            return new JsonResponse([], Response::HTTP_BAD_REQUEST);
        }

        /** @var Adherent $adherent */
        $adherent = $this->getUser();

        $committees = $this->getCommittees($adherent, $term, $committeeRepository);

        foreach ($committees as $committee) {
            $result[] = [
                'uuid' => $committee->getUuid()->toString(),
                'name' => $committee->getName(),
            ];
        }

        return new JsonResponse($result ?? []);
    }

    #[Route(path: '/mon-equipe/autocompletion/ville', name: 'autocomplete_city', condition: 'request.isXmlHttpRequest()', methods: ['GET'])]
    public function cityAutocompleteAction(
        Request $request,
        FranceCities $franceCities,
        ZoneRepository $zoneRepository
    ): JsonResponse {
        if (!$search = $request->query->get('search')) {
            return new JsonResponse([], Response::HTTP_BAD_REQUEST);
        }

        // refTags migration: keep only 'else' logic after the migration is completed
        if ($this->getManagedTags($this->getUser())) {
            $foundedCities = $franceCities->searchCitiesForZones($this->getZones($this->getUser()), $search);
        } else {
            $foundedCities = $zoneRepository->searchCitiesInZones($this->getZones($this->getUser()), $search);
        }

        $result = [];
        foreach ($foundedCities as $city) {
            $result[] = ['name' => $city->getName(), 'insee_code' => $city->getCode(), 'postal_code' => $city->getPostalCodeAsString()];
        }

        return new JsonResponse($result);
    }

    abstract protected function getSpaceType(): string;

    abstract protected function getCommittees(
        Adherent $adherent,
        string $term,
        CommitteeRepository $committeeRepository
    ): array;

    // refTags migration: remove when the migration is completed
    abstract protected function getManagedTags(Adherent $adherent): array;

    abstract protected function getZones(Adherent $adherent): array;

    private function renderTemplate(string $template, array $parameters = [])
    {
        return $this->render($template, array_merge(
            $parameters,
            [
                'base_template' => sprintf('my_team/_base_%s_space.html.twig', $spaceName = $this->getSpaceType()),
                'space_name' => $spaceName,
            ]
        ));
    }
}
