<?php

namespace App\Controller\EnMarche\Committee;

use App\Committee\Filter\ListFilter;
use App\Controller\EnMarche\AccessDelegatorTrait;
use App\Form\Committee\CommitteeFilterType;
use App\Geo\ManagedZoneProvider;
use App\Repository\CommitteeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

abstract class AbstractCommitteeController extends Controller
{
    use AccessDelegatorTrait;

    /**
     * @Route("", name="committees")
     */
    public function committeesAction(
        Request $request,
        CommitteeRepository $committeeRepository,
        ManagedZoneProvider $managedZoneProvider
    ): Response {
        $managedZones = $managedZoneProvider->getManagedZones($this->getMainUser($request->getSession()), $this->getSpaceType());
        $filter = new ListFilter($managedZones);

        $form = $this->createFilterForm($filter);
        $form->handleRequest($request);
        if ($form->isSubmitted() && !$form->isValid()) {
            $filter = new ListFilter($managedZones);
        }

        return $this->render('referent/committees_list.html.twig', [
            'committees' => $committeeRepository->searchByFilter($filter),
            'base_template' => sprintf('committee/_base_%s.html.twig', $spaceName = $this->getSpaceType()),
            'space_name' => $spaceName,
            'form' => $form->createView(),
            'total_count' => $committeeRepository->countForZones($managedZones),
        ]);
    }

    protected function createFilterForm(ListFilter $filter): FormInterface
    {
        return $this->createForm(CommitteeFilterType::class, $filter, [
            'space_type' => $this->getSpaceType(),
            'method' => Request::METHOD_GET,
            'csrf_protection' => false,
        ]);
    }

    abstract protected function getSpaceType(): string;
}
