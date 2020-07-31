<?php

namespace App\Controller\EnMarche\ElectedRepresentative;

use App\Controller\EnMarche\AccessDelegatorTrait;
use App\ElectedRepresentative\Filter\ListFilter;
use App\Entity\Adherent;
use App\Entity\ElectedRepresentative\ElectedRepresentative;
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
        $filter = new ListFilter($managedTags = $this->getManagedTags($this->getMainUser($request->getSession())));

        $form = $this
            ->createFilterForm($managedTags, $filter)
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && !$form->isValid()) {
            $filter = new ListFilter($managedTags);
        }

        $electedRepresentatives = $electedRepresentativeRepository->searchByFilter($filter, $request->query->getInt('page', 1));

        return $this->renderTemplate('elected_representative/list.html.twig', [
            'elected_representatives' => $electedRepresentatives,
            'filter' => $filter,
            'form' => $form->createView(),
            'total_count' => $electedRepresentativeRepository->countForReferentTags($filter->getReferentTags()),
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

    abstract protected function getManagedTags(Adherent $adherent): array;

    protected function createFilterForm(array $managedTags, ListFilter $filter = null): FormInterface
    {
        return $this->createForm(ElectedRepresentativeFilterType::class, $filter, [
            'referent_tags' => $managedTags,
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
