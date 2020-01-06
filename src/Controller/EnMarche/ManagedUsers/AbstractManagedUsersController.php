<?php

namespace AppBundle\Controller\EnMarche\ManagedUsers;

use AppBundle\ManagedUsers\ManagedUsersFilter;
use AppBundle\Repository\Projection\ReferentManagedUserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

abstract class AbstractManagedUsersController extends AbstractController
{
    private $managedUsersRepository;

    public function __construct(ReferentManagedUserRepository $managedUsersRepository)
    {
        $this->managedUsersRepository = $managedUsersRepository;
    }

    /**
     * @Route("/utilisateurs", name="list", methods={"GET"})
     */
    public function listAction(Request $request): Response
    {
        $form = $this
            ->createFilterForm($filter = $this->createFilterModel())
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && !$form->isValid()) {
            $filter = $this->createFilterModel();
        }

        $users = $this->managedUsersRepository->searchByFilter($filter, $request->query->getInt('page', 1));

        return $this->renderTemplate('managed_users/list.html.twig', [
            'form' => $form->createView(),
            'users' => $users,
            'filter' => $filter,
            'total_count' => $this->managedUsersRepository->countManagedUsers($filter->getReferentTags()),
        ]);
    }

    abstract protected function getSpaceType(): string;

    abstract protected function createFilterModel(): ManagedUsersFilter;

    abstract protected function createFilterForm(ManagedUsersFilter $filter = null): FormInterface;

    private function renderTemplate(string $template, array $parameters = []): Response
    {
        return $this->render($template, array_merge(
            $parameters,
            [
                'base_template' => sprintf('managed_users/_base_%s_space.html.twig', $spaceName = $this->getSpaceType()),
                'space_name' => $spaceName,
            ]
        ));
    }
}
