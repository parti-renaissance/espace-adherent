<?php

namespace App\Controller\EnMarche\MyTeam;

use App\Controller\CanaryControllerTrait;
use App\Entity\Adherent;
use App\Entity\MyTeam\DelegatedAccess;
use App\Form\MyTeam\DelegateAccessType;
use App\Form\MyTeam\MyTeamSearchAdherentType;
use App\Intl\FranceCitiesBundle;
use App\Repository\AdherentRepository;
use App\Repository\CommitteeRepository;
use App\Repository\MyTeam\DelegatedAccessRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

abstract class AbstractMyTeamController extends Controller
{
    use CanaryControllerTrait;

    /**
     * @Route("", name="list")
     */
    public function list(DelegatedAccessRepository $delegatedAccessRepository): Response
    {
        $this->disableInProduction();

        $delegatedAccesses = $delegatedAccessRepository->findBy(['delegator' => $this->getUser()]);

        return $this->renderTemplate('my_team/list.html.twig', [
            'delegatedAccesses' => $delegatedAccesses,
        ]);
    }

    /**
     * @Route("/deleguer-acces", name="delegate_access", methods={"GET", "POST"})
     * @Route("/deleguer-acces/{id}", name="delegate_access_edit", methods={"GET", "POST"})
     */
    public function delegateAccess(
        Request $request,
        EntityManagerInterface $entityManager,
        DelegatedAccess $delegatedAccess = null
    ): Response {
        $this->disableInProduction();

        $delegatedAccess = $delegatedAccess ?? new DelegatedAccess();
        $delegatedAccess->setDelegator($this->getUser());
        $delegatedAccess->setType($this->getSpaceType());

        $filterForm = $this->createForm(MyTeamSearchAdherentType::class);

        $form = $this->createForm(DelegateAccessType::class, $delegatedAccess, ['type' => $this->getSpaceType()]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($delegatedAccess);
            $entityManager->flush();

            return $this->redirectToRoute(sprintf('app_%s_my_team_list', $this->getSpaceType()));
        }

        return $this->renderTemplate('my_team/delegate_access.html.twig', [
            'filter_form' => $filterForm->createView(),
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/search", name="search", methods={"POST"})
     */
    public function search(Request $request, AdherentRepository $adherentRepository): Response
    {
        $form = $this->createForm(MyTeamSearchAdherentType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $users = $adherentRepository->findAdherentsByName($this->getManagedTags($this->getUser()), $form->get('name')->getData());
        }

        return $this->renderTemplate('my_team/search_results.html.twig', [
            'form' => $form->createView(),
            'users' => $users ?? [],
        ]);
    }

    /**
     * @Route("/mon-equipe/autocompletion/comite",
     *     name="autocomplete_committee",
     *     condition="request.isXmlHttpRequest()",
     *     methods={"GET"}
     * )
     */
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

    /**
     * @Route("/mon-equipe/autocompletion/ville",
     *     name="autocomplete_city",
     *     condition="request.isXmlHttpRequest()",
     *     methods={"GET"}
     * )
     */
    public function cityAutocompleteAction(Request $request): JsonResponse
    {
        if (!$search = $request->query->get('search')) {
            return new JsonResponse([], Response::HTTP_BAD_REQUEST);
        }

        return new JsonResponse(FranceCitiesBundle::searchCitiesForTags($this->getManagedTags($this->getUser()), $search));
    }

    abstract protected function getSpaceType(): string;

    abstract protected function getCommittees(
        Adherent $adherent,
        string $term,
        CommitteeRepository $committeeRepository
    ): array;

    abstract protected function getManagedTags(Adherent $adherent): array;

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
