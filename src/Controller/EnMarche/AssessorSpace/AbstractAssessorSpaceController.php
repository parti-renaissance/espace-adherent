<?php

namespace AppBundle\Controller\EnMarche\AssessorSpace;

use ApiPlatform\Core\DataProvider\PaginatorInterface;
use AppBundle\Assessor\AssessorRole\AssessorAssociationManager;
use AppBundle\Assessor\Filter\AssessorRequestExportFilter;
use AppBundle\Assessor\Filter\AssociationVotePlaceFilter;
use AppBundle\Controller\CanaryControllerTrait;
use AppBundle\Entity\VotePlace;
use AppBundle\Exporter\AssessorsExporter;
use AppBundle\Form\AssessorVotePlaceListType;
use AppBundle\Repository\VotePlaceRepository;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

abstract class AbstractAssessorSpaceController extends Controller
{
    use CanaryControllerTrait;

    protected const PAGE_LIMIT = 10;

    protected $votePlaceRepository;

    public function __construct(VotePlaceRepository $votePlaceRepository)
    {
        $this->votePlaceRepository = $votePlaceRepository;
    }

    /**
     * @Route("/bureaux-de-vote", name="_attribution_form", methods={"GET", "POST"})
     */
    public function votePlaceAttributionAction(Request $request, AssessorAssociationManager $manager): Response
    {
        $this->disableInProduction();

        $filterForm = $this->createFilterForm($filter = $this->createFilter())->handleRequest($request);

        if ($filterForm->isSubmitted() && !$filterForm->isValid()) {
            $filter = $this->createFilter();
        }

        $paginator = $this->getVotePlacesPaginator($request->query->getInt('page', 1), $filter);

        $form = $this
            ->createForm(AssessorVotePlaceListType::class, $manager->getAssociationValueObjectsFromVotePlaces(
                iterator_to_array($paginator)
            ))
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            $manager->handleUpdate($form->getData());

            $this->addFlash('info', 'Modifications ont bien été sauvegardés');

            return $this->redirectToRoute(
                sprintf('app_assessors_%s_attribution_form', $this->getSpaceType()),
                $this->getRouteParams($request)
            );
        }

        return $this->renderTemplate('assessor_space/attribution_form.html.twig', [
            'vote_places' => $paginator,
            'form' => $form->createView(),
            'filter_form' => $filterForm->createView(),
            'route_params' => $this->getRouteParams($request),
        ]);
    }

    /**
     * @Route("/export.{_format}", name="_export", methods={"GET"}, defaults={"_format": "xls"}, requirements={"_format": "csv|xls"})
     */
    public function exportAssessorsAction(string $_format, AssessorsExporter $exporter): Response
    {
        $this->disableInProduction();

        return $exporter->getResponse($_format, $this->getExportFilter());
    }

    protected function renderTemplate(string $template, array $parameters = []): Response
    {
        return $this->render($template, array_merge(
            $parameters,
            [
                'base_template' => sprintf('assessor_space/_base_%s_space.html.twig', $spaceType = $this->getSpaceType()),
                'space_type' => $spaceType,
            ]
        ));
    }

    abstract protected function getSpaceType(): string;

    abstract protected function getExportFilter(): AssessorRequestExportFilter;

    abstract protected function createFilterForm(AssociationVotePlaceFilter $filter): FormInterface;

    abstract protected function createFilter(): AssociationVotePlaceFilter;

    /**
     * @return VotePlace[]|PaginatorInterface
     */
    private function getVotePlacesPaginator(int $page, AssociationVotePlaceFilter $filter): PaginatorInterface
    {
        return $this->votePlaceRepository->findAllForFilter($filter, $page, self::PAGE_LIMIT);
    }

    private function getRouteParams(Request $request): array
    {
        $params = [];

        if ($request->query->has('f')) {
            $params['f'] = (array) $request->query->get('f');
        }

        if ($request->query->has('page')) {
            $params['page'] = $request->query->getInt('page');
        }

        return $params;
    }
}
