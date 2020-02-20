<?php

namespace AppBundle\Controller\EnMarche\AssessorSpace;

use AppBundle\Assessor\Filter\AssessorRequestExportFilter;
use AppBundle\Assessor\Filter\AssociationVotePlaceFilter;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\Election;
use AppBundle\Form\Assessor\ReferentVotePlaceFilterType;
use AppBundle\Form\MunicipalManagerCityListType;
use AppBundle\Form\ReferentCityFilterType;
use AppBundle\MunicipalManager\Filter\AssociationCityFilter;
use AppBundle\MunicipalManager\MunicipalManagerRole\MunicipalManagerAssociationManager;
use AppBundle\Repository\CityRepository;
use Doctrine\ORM\Query;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(path="/espace-referent/assesseurs", name="app_assessors_referent")
 *
 * @Security("is_granted('ROLE_REFERENT')")
 */
class ReferentAssessorSpaceController extends AbstractAssessorSpaceController
{
    private const SPACE_NAME = 'referent';

    /**
     * @Route("/communes", name="_municipal_manager_attribution_form", methods={"GET", "POST"})
     */
    public function municipalManagerAttributionAction(
        Request $request,
        CityRepository $cityRepository,
        MunicipalManagerAssociationManager $manager
    ): Response {
        $this->disableInProduction();

        $filterForm = $this
            ->createForm(ReferentCityFilterType::class, $filter = $this->createCityFilter())
            ->handleRequest($request)
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
                'app_assessors_referent_municipal_manager_attribution_form',
                $this->getRouteParams($request)
            );
        }

        return $this->render('referent/municipal_manager/attribution_form.html.twig', [
            'cities' => $paginator,
            'form' => $form->createView(),
            'filter_form' => $filterForm->createView(),
            'route_params' => $this->getRouteParams($request),
        ]);
    }

    private function createCityFilter(): AssociationCityFilter
    {
        $filter = new AssociationCityFilter();
        $filter->setTags($this->getUser()->getManagedArea()->getTags()->toArray());

        return $filter;
    }

    protected function getSpaceType(): string
    {
        return self::SPACE_NAME;
    }

    protected function getExportFilter(): AssessorRequestExportFilter
    {
        return new AssessorRequestExportFilter(
            $this->getUser()->getManagedArea()->getTags()->toArray()
        );
    }

    protected function createFilterForm(AssociationVotePlaceFilter $filter): FormInterface
    {
        return $this->createForm(ReferentVotePlaceFilterType::class, $filter);
    }

    protected function createFilter(): AssociationVotePlaceFilter
    {
        $filter = new AssociationVotePlaceFilter();

        $filter->setTags($this->getReferentTags());

        return $filter;
    }

    protected function getVoteResultsExportQuery(Election $election): Query
    {
        return $this->voteResultRepository->getReferentExportQuery($election, $this->getReferentTags());
    }

    private function getReferentTags(): array
    {
        /** @var Adherent $referent */
        $referent = $this->getUser();

        return $referent->getManagedArea()->getTags()->toArray();
    }
}
