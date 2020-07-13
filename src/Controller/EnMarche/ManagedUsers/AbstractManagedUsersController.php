<?php

namespace App\Controller\EnMarche\ManagedUsers;

use App\Controller\EnMarche\AccessDelegatorTrait;
use App\Exporter\ManagedUsersExporter;
use App\ManagedUsers\ManagedUsersFilter;
use App\Repository\Projection\ManagedUserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

abstract class AbstractManagedUsersController extends Controller
{
    use AccessDelegatorTrait;

    private $managedUsersRepository;

    public function __construct(ManagedUserRepository $managedUsersRepository)
    {
        $this->managedUsersRepository = $managedUsersRepository;
    }

    /**
     * @Route("/utilisateurs.{_format}", name="list", methods={"GET"}, defaults={"_format": "html"}, requirements={"_format": "html|csv|xls"})
     */
    public function listAction(Request $request, string $_format, ManagedUsersExporter $exporter): Response
    {
        $form = $this
            ->createFilterForm($filter = $this->createFilterModel($request))
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && !$form->isValid()) {
            $filter = $this->createFilterModel($request);
        }

        if ('html' !== $_format) {
            return $exporter->getResponse($_format, $filter);
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

    abstract protected function createFilterModel(Request $request): ManagedUsersFilter;

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
