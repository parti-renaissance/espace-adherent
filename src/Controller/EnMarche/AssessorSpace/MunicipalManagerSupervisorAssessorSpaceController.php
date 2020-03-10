<?php

namespace AppBundle\Controller\EnMarche\AssessorSpace;

use AppBundle\Assessor\Filter\AssessorRequestExportFilter;
use AppBundle\Assessor\Filter\AssociationVotePlaceFilter;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\Election;
use AppBundle\Form\Assessor\ReferentVotePlaceFilterType;
use Doctrine\ORM\Query;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(path="/espace-responsable-attribution/assesseurs", name="app_assessors_municipal_manager_supervisor")
 *
 * @Security("is_granted('ROLE_MUNICIPAL_MANAGER_SUPERVISOR')")
 */
class MunicipalManagerSupervisorAssessorSpaceController extends AbstractAssessorSpaceController
{
    private const SPACE_NAME = 'municipal_manager_supervisor';

    protected function getSpaceType(): string
    {
        return self::SPACE_NAME;
    }

    protected function getAssessorRequestExportFilter(): AssessorRequestExportFilter
    {
        return new AssessorRequestExportFilter($this->getReferentTags());
    }

    protected function createVotePlaceListFilterForm(AssociationVotePlaceFilter $filter): FormInterface
    {
        return $this->createForm(ReferentVotePlaceFilterType::class, $filter);
    }

    protected function createVotePlaceListFilter(): AssociationVotePlaceFilter
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
        /** @var Adherent $adherent */
        $adherent = $this->getUser();

        return $adherent
            ->getMunicipalManagerSupervisorRole()
            ->getReferent()
            ->getManagedArea()
            ->getTags()
            ->toArray()
        ;
    }
}
