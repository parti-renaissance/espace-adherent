<?php

namespace AppBundle\Controller\EnMarche\AssessorSpace;

use AppBundle\Assessor\Filter\AssessorRequestExportFilter;
use AppBundle\Assessor\Filter\AssociationVotePlaceFilter;
use AppBundle\Entity\Adherent;
use AppBundle\Form\Assessor\ReferentVotePlaceFilterType;
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

    protected function getVoteResultsExportQuery(): Query
    {
        return $this->voteResultRepository->getReferentExportQuery($this->getReferentTags());
    }

    private function getReferentTags(): array
    {
        /** @var Adherent $referent */
        $referent = $this->getUser();

        return $referent->getManagedArea()->getTags()->toArray();
    }
}
