<?php

namespace AppBundle\Controller\EnMarche\Election\VoteResultList;

use AppBundle\Entity\Election\VoteResultListCollection;
use AppBundle\Form\VoteResultListCollectionType;
use AppBundle\Repository\Election\VoteResultListCollectionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

abstract class AbstractVoteResultListController extends Controller
{
    /** @var EntityManagerInterface */
    private $entityManager;
    /** @var VoteResultListCollectionRepository */
    private $voteResultListCollectionRepository;

    protected function submitListFormAction(
        Request $request,
        array $cities,
        ?VoteResultListCollection $listCollection
    ): Response {
        $form = $this
            ->createForm(VoteResultListCollectionType::class, $listCollection)
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var VoteResultListCollection $listCollection */
            $listCollection = $form->getData();
            $listCollection->mergeCities($cities);

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

    /**
     * @required
     */
    public function setEntityManager(EntityManagerInterface $entityManager): void
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @required
     */
    public function setVoteResultListCollectionRepository(
        VoteResultListCollectionRepository $voteResultListCollectionRepository
    ): void {
        $this->voteResultListCollectionRepository = $voteResultListCollectionRepository;
    }

    protected function findListCollection(array $cities): ?VoteResultListCollection
    {
        if (!$cities) {
            throw new \InvalidArgumentException('City array can not be empty');
        }

        return $this->voteResultListCollectionRepository->findOneByCities($cities);
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
