<?php

namespace AppBundle\Controller\EnMarche\AssessorSpace;

use AppBundle\Assessor\Filter\AssessorRequestExportFilter;
use AppBundle\Assessor\Filter\AssociationVotePlaceFilter;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\Election;
use AppBundle\Form\Assessor\DefaultVotePlaceFilterType;
use AppBundle\Intl\FranceCitiesBundle;
use Doctrine\ORM\Query;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(path="/espace-municipales-2020/assesseurs", name="app_assessors_municipal_chief")
 *
 * @Security("is_granted('ROLE_MUNICIPAL_CHIEF')")
 */
class MunicipalChiefAssessorSpaceController extends AbstractAssessorSpaceController
{
    private const SPACE_NAME = 'municipal_chief';

    protected function getSpaceType(): string
    {
        return self::SPACE_NAME;
    }

    protected function getAssessorRequestExportFilter(): AssessorRequestExportFilter
    {
        $cityData = FranceCitiesBundle::getCityDataFromInseeCode(
            $inseeCode = $this->getMunicipalChiefZoneInseeCode()
        );

        if (!$cityData) {
            throw new \InvalidArgumentException(sprintf('[MunicipalChief] City with insee code "%s" is not identified', $inseeCode));
        }

        return new AssessorRequestExportFilter([], [$cityData['postal_code']]);
    }

    protected function createVotePlaceListFilter(): AssociationVotePlaceFilter
    {
        $filter = new AssociationVotePlaceFilter();

        $filter->setInseeCodes([$this->getUser()->getMunicipalChiefManagedArea()->getInseeCode()]);

        return $filter;
    }

    protected function createVotePlaceListFilterForm(AssociationVotePlaceFilter $filter): FormInterface
    {
        return $this->createForm(DefaultVotePlaceFilterType::class, $filter);
    }

    protected function getVoteResultsExportQuery(Election $election): Query
    {
        return $this->voteResultRepository->getExportQueryByInseeCode($election, $this->getMunicipalChiefZoneInseeCode());
    }

    private function getMunicipalChiefZoneInseeCode(): string
    {
        /** @var Adherent $adherent */
        return $this->getUser()->getMunicipalChiefManagedArea()->getInseeCode();
    }
}
