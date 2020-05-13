<?php

namespace App\Controller\EnMarche\AssessorSpace;

use App\Assessor\Filter\AssessorRequestExportFilter;
use App\Assessor\Filter\AssociationVotePlaceFilter;
use App\Entity\Adherent;
use App\Entity\Election;
use App\Form\Assessor\DefaultVotePlaceFilterType;
use Doctrine\ORM\Query;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(path="/espace-responsable-communal/assesseurs", name="app_assessors_municipal_manager")
 *
 * @Security("is_granted('ROLE_MUNICIPAL_MANAGER')")
 */
class MunicipalManagerAssessorSpaceController extends AbstractAssessorSpaceController
{
    private const SPACE_NAME = 'municipal_manager';

    protected function getSpaceType(): string
    {
        return self::SPACE_NAME;
    }

    protected function getAssessorRequestExportFilter(): AssessorRequestExportFilter
    {
        return new AssessorRequestExportFilter([], $this->getMunicipalManagerZonePostalCodes());
    }

    protected function createVotePlaceListFilter(): AssociationVotePlaceFilter
    {
        $filter = new AssociationVotePlaceFilter();

        $filter->setInseeCodes($this->getMunicipalManagerZoneInseeCodes());

        return $filter;
    }

    protected function createVotePlaceListFilterForm(AssociationVotePlaceFilter $filter): FormInterface
    {
        return $this->createForm(DefaultVotePlaceFilterType::class, $filter);
    }

    protected function getVoteResultsExportQuery(Election $election): Query
    {
        return $this->voteResultRepository->getExportQueryByInseeCodes(
            $election,
            $this->getMunicipalManagerZoneInseeCodes()
        );
    }

    private function getMunicipalManagerZonePostalCodes(): array
    {
        /** @var Adherent $adherent */
        $adherent = $this->getUser();

        return $adherent->getMunicipalManagerRole()->getPostalCodes();
    }

    private function getMunicipalManagerZoneInseeCodes(): array
    {
        /** @var Adherent $adherent */
        $adherent = $this->getUser();

        return $adherent->getMunicipalManagerRole()->getInseeCodes();
    }
}
