<?php

namespace AppBundle\Controller\EnMarche\Election\VoteResultList;

use AppBundle\Election\ElectionManager;
use AppBundle\Entity\City;
use AppBundle\Entity\Election\VoteResultListCollection;
use AppBundle\Form\VoteResultListCollectionType;
use AppBundle\Repository\Election\VoteResultListCollectionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

abstract class AbstractVoteResultListController extends Controller
{
    /** @var EntityManagerInterface */
    private $entityManager;
    /** @var VoteResultListCollectionRepository */
    private $voteResultListCollectionRepository;
    /** @var ElectionManager */
    private $electionManager;

    public function __construct(
        EntityManagerInterface $entityManager,
        VoteResultListCollectionRepository $voteResultListCollectionRepository,
        ElectionManager $electionManager
    ) {
        $this->entityManager = $entityManager;
        $this->voteResultListCollectionRepository = $voteResultListCollectionRepository;
        $this->electionManager = $electionManager;
    }

    /**
     * @Route("/{id}/listes", name="_vote_result_list_edit", methods={"GET", "POST"})
     */
    public function __invoke(City $city, Request $request): Response
    {
        $electionRound = $this->electionManager->getClosestElectionRound();

        if (!$listCollection = $this->voteResultListCollectionRepository->findOneByCity($city, $electionRound)) {
            $listCollection = new VoteResultListCollection($city, $electionRound);
        }

        $form = $this
            ->createForm(VoteResultListCollectionType::class, $listCollection)
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var VoteResultListCollection $listCollection */
            if (!$listCollection->getId()) {
                $this->entityManager->persist($listCollection);
            }

            $this->entityManager->flush();
            $this->addFlash('info', 'Les modifications ont bien été enregistrées');

            return $this->getSuccessRedirectionResponse();
        }

        return $this->renderTemplate('election_result_list/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    protected function getSuccessRedirectionResponse(): RedirectResponse
    {
        return $this->redirectToRoute(sprintf('app_assessors_%s_attribution_form', $this->getSpaceType()));
    }

    protected function renderTemplate(string $template, array $parameters = []): Response
    {
        return $this->render($template, array_merge(
            $parameters,
            [
                'base_template' => sprintf('election_result_list/_base_%s_space.html.twig', $spaceType = $this->getSpaceType()),
                'space_type' => $spaceType,
            ]
        ));
    }

    abstract protected function getSpaceType(): string;
}
