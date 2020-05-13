<?php

namespace App\Controller\EnMarche\AssessorSpace;

use App\Assessor\Filter\AssessorRequestExportFilter;
use App\Assessor\Filter\AssociationVotePlaceFilter;
use App\Entity\Adherent;
use App\Entity\Election;
use App\Form\Assessor\ReferentVotePlaceFilterType;
use Doctrine\ORM\Query;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(path="/espace-referent/assesseurs", name="app_assessors_referent")
 *
 * @Security("is_granted('ROLE_REFERENT')")
 */
class ReferentAssessorSpaceController extends AbstractAssessorSpaceController
{
    private const SPACE_NAME = 'referent';

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
        /** @var Adherent $referent */
        $referent = $this->getUser();

        return $referent->getManagedArea()->getTags()->toArray();
    }
}
