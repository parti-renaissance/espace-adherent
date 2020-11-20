<?php

namespace App\Controller\EnMarche\ElectedRepresentative;

use App\Controller\EnMarche\AccessDelegatorTrait;
use App\ElectedRepresentative\Filter\ListFilter;
use App\Entity\Adherent;
use App\Entity\ElectedRepresentative\ElectedRepresentative;
use App\Entity\Geo\Zone;
use App\Entity\UserListDefinitionEnum;
use App\Form\ElectedRepresentative\ElectedRepresentativeFilterType;
use App\Repository\ElectedRepresentative\ElectedRepresentativeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

abstract class AbstractElectedRepresentativeController extends Controller
{
    use AccessDelegatorTrait;

    /**
     * @Route("/elus", name="list", methods={"GET"})
     */
    public function listElectedRepresentatives(
        Request $request,
        ElectedRepresentativeRepository $electedRepresentativeRepository
    ): Response {
        $filter = new ListFilter($managedZones = $this->getManagedZones($this->getMainUser($request->getSession())));

        $form = $this
            ->createFilterForm($filter)
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && !$form->isValid()) {
            $filter = new ListFilter($managedZones);
        }

        $electedRepresentatives = $electedRepresentativeRepository->searchByFilter($filter, $request->query->getInt('page', 1), 50);

        return $this->renderTemplate('elected_representative/list.html.twig', [
            'elected_representatives' => $electedRepresentatives,
            'filter' => $filter,
            'form' => $form->createView(),
            'total_count' => $electedRepresentativeRepository->countForZones($managedZones),
        ]);
    }

    /**
     * @Route("/elus/{uuid}", name="show", methods={"GET"})
     */
    public function showElectedRepresentative(ElectedRepresentative $electedRepresentative): Response
    {
        return $this->renderTemplate('elected_representative/show.html.twig', [
            'elected_representative' => $electedRepresentative,
        ]);
    }

    abstract protected function getSpaceType(): string;

    /**
     * @return Zone[]
     */
    abstract protected function getManagedZones(Adherent $adherent): array;

    protected function createFilterForm(ListFilter $filter = null): FormInterface
    {
        return $this->createForm(ElectedRepresentativeFilterType::class, $filter, [
            'space_type' => $this->getSpaceType(),
            'user_list_definition_type' => [UserListDefinitionEnum::TYPE_ELECTED_REPRESENTATIVE],
            'method' => Request::METHOD_GET,
            'csrf_protection' => false,
        ]);
    }

    protected function renderTemplate(string $template, array $parameters = []): Response
    {
        return $this->render($template, array_merge(
            $parameters,
            [
                'base_template' => sprintf('elected_representative/_base_%s.html.twig', $spaceName = $this->getSpaceType()),
                'space_name' => $spaceName,
            ]
        ));
    }
}
