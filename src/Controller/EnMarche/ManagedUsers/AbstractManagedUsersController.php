<?php

namespace App\Controller\EnMarche\ManagedUsers;

use App\Controller\EnMarche\AccessDelegatorTrait;
use App\Entity\Adherent;
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
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

abstract class AbstractManagedUsersController extends AbstractController
{
    use AccessDelegatorTrait;

    public function __construct(
        private readonly ManagedUserRepository $managedUsersRepository,
        private readonly ManagedZoneProvider $managedZoneProvider,
    ) {
    }

    #[Route(path: '/utilisateurs.{_format}', name: 'list', methods: ['GET'], defaults: ['_format' => 'html'], requirements: ['_format' => 'html|csv|xlsx'])]
    public function listAction(Request $request, string $_format, ManagedUsersExporter $exporter): Response
    {
        $session = $request->getSession();
        $user = $this->getMainUser($session);

        $form = $this
            ->createFilterForm($filter = $this->createFilterModel($request->getSession(), $user), $user)
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && !$form->isValid()) {
            $filter = $this->createFilterModel($request->getSession(), $user);
        }

        if ('html' !== $_format) {
            return $exporter->getResponse($_format, $filter, $this->getSpaceType());
        }

        $users = $this->managedUsersRepository->searchByFilter($filter, $request->query->getInt('page', 1));

        return $this->renderTemplate('managed_users/list.html.twig', [
            'form' => $form->createView(),
            'users' => $users,
            'filter' => $filter,
            'total_count' => $this->managedUsersRepository->countManagedUsers($filter->managedZones),
        ]);
    }

    abstract protected function getSpaceType(): string;

    abstract protected function getScopeCode(): string;

    protected function createFilterModel(SessionInterface $session, Adherent $adherent): ManagedUsersFilter
    {
        $model = ManagedUsersFilterFactory::createForZones(
            $this->getScopeCode(),
            $this->managedZoneProvider->getManagedZones($adherent, $this->getSpaceType())
        );

        $model->committeeUuids = $this->getRestrictedCommittees($session);
        $model->cities = $this->getRestrictedCities($session);
        $model->onlyJeMengageUsers = false;

        return $model;
    }

    protected function createFilterForm(ManagedUsersFilter $filter, Adherent $mainAdherent): FormInterface
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
                'base_template' => \sprintf('managed_users/_base_%s_space.html.twig', $spaceName = $this->getSpaceType()),
                'space_name' => $spaceName,
            ]
        ));
    }
}
