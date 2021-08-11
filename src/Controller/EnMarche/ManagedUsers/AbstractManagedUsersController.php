<?php

namespace App\Controller\EnMarche\ManagedUsers;

use App\Controller\EnMarche\AccessDelegatorTrait;
use App\Exporter\ManagedUsersExporter;
use App\Form\ManagedUsers\ManagedUsersFilterType;
use App\Geo\ManagedZoneProvider;
use App\ManagedUsers\ManagedUsersFilter;
use App\ManagedUsers\ManagedUsersFilterFactory;
use App\Repository\Projection\ManagedUserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

abstract class AbstractManagedUsersController extends AbstractController
{
    use AccessDelegatorTrait;

    private $managedUsersRepository;
    private $filterFactory;
    private $managedZoneProvider;

    public function __construct(
        ManagedUserRepository $managedUsersRepository,
        ManagedZoneProvider $managedZoneProvider,
        ManagedUsersFilterFactory $filterFactory
    ) {
        $this->managedUsersRepository = $managedUsersRepository;
        $this->managedZoneProvider = $managedZoneProvider;
        $this->filterFactory = $filterFactory;
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
            return $exporter->getResponse($_format, $filter, $this->getSpaceType());
        }

        $users = $this->managedUsersRepository->searchByFilter($filter, $request->query->getInt('page', 1));

        return $this->renderTemplate('managed_users/list.html.twig', [
            'form' => $form->createView(),
            'users' => $users,
            'filter' => $filter,
            'total_count' => $this->managedUsersRepository->countManagedUsers($filter->getManagedZones()),
        ]);
    }

    abstract protected function getSpaceType(): string;

    protected function createFilterModel(Request $request): ManagedUsersFilter
    {
        $session = $request->getSession();

        $model = $this->filterFactory->create($this->getSpaceType(), $this->getMainUser($session));

        $model->setCommitteeUuids($this->getRestrictedCommittees($session));
        $model->setCities($this->getRestrictedCities($session));

        return $model;
    }

    protected function createFilterForm(ManagedUsersFilter $filter = null): FormInterface
    {
        return $this->createForm(ManagedUsersFilterType::class, $filter, [
            'method' => Request::METHOD_GET,
            'csrf_protection' => false,
            'space_type' => $this->getSpaceType(),
        ]);
    }

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
