<?php

namespace AppBundle\Controller\EnMarche\MunicipalManagerAttribution;

use AppBundle\Form\MunicipalManagerCityListType;
use AppBundle\Form\ReferentCityFilterType;
use AppBundle\MunicipalManager\Filter\AssociationCityFilter;
use AppBundle\MunicipalManager\MunicipalManagerRole\MunicipalManagerAssociationManager;
use AppBundle\Repository\CityRepository;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

abstract class AbstractMunicipalManagerAttributionController extends Controller
{
    protected const PAGE_LIMIT = 10;

    /**
     * @Route("/responsables-communaux", name="_attribution_form", methods={"GET", "POST"})
     */
    public function municipalManagerAttributionAction(
        Request $request,
        CityRepository $cityRepository,
        MunicipalManagerAssociationManager $manager
    ): Response {
        // Transforms actual request (`POST`, ...) in `GET` request for passing it to $filterForm::handleRequest method
        $filterRequest = $request->duplicate(null, []);
        $filterRequest->setMethod(Request::METHOD_GET);

        $filterForm = $this
            ->createForm(ReferentCityFilterType::class, $filter = $this->createCityFilter())
            ->handleRequest($filterRequest)
        ;

        if ($filterForm->isSubmitted() && !$filterForm->isValid()) {
            $filter = $this->createCityFilter();
        }

        $paginator = $cityRepository->findAllForFilter(
            $filter,
            $request->query->getInt('page', 1),
            self::PAGE_LIMIT
        );

        $form = $this
            ->createForm(MunicipalManagerCityListType::class, $manager->getAssociationValueObjectsFromCities(
                iterator_to_array($paginator)
            ))
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            $manager->handleUpdate($form->getData());

            $this->addFlash('info', 'Les modifications ont bien été sauvegardées');

            return $this->redirectToRoute(
                sprintf('app_municipal_manager_%s_attribution_form', static::getSpaceType()),
                $this->getRouteParams($request)
            );
        }

        return $this->renderTemplate('municipal_manager_attribution/index.html.twig', [
            'cities' => $paginator,
            'form' => $form->createView(),
            'filter_form' => $filterForm->createView(),
            'route_params' => $this->getRouteParams($request),
        ]);
    }

    protected function getRouteParams(Request $request): array
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

    protected function renderTemplate(string $template, array $parameters = []): Response
    {
        return $this->render($template, array_merge(
            $parameters,
            [
                'base_template' => sprintf('municipal_manager_attribution/_base_%s_space.html.twig', $spaceType = static::getSpaceType()),
                'space_type' => $spaceType,
            ]
        ));
    }

    abstract protected function createCityFilter(): AssociationCityFilter;

    abstract protected function getSpaceType(): string;
}
