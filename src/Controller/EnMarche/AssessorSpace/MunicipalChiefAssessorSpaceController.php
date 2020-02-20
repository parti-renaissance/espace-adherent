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

    protected function getExportFilter(): AssessorRequestExportFilter
    {
        return new AssessorRequestExportFilter([], [$this->getMunicipalChiefZonePostalCode()]);
    }

    protected function createFilter(): AssociationVotePlaceFilter
    {
        $filter = new AssociationVotePlaceFilter();

        $filter->setPostalCode($this->getMunicipalChiefZonePostalCode());

        return $filter;
    }

    protected function createFilterForm(AssociationVotePlaceFilter $filter): FormInterface
    {
        return $this->createForm(DefaultVotePlaceFilterType::class, $filter);
    }

    protected function getVoteResultsExportQuery(Election $election): Query
    {
        return $this->voteResultRepository->getMunicipalChiefExportQuery($election, $this->getMunicipalChiefZonePostalCode());
    }

    private function getMunicipalChiefZonePostalCode(): string
    {
        /** @var Adherent $adherent */
        $adherent = $this->getUser();

        $cityData = FranceCitiesBundle::getCityDataFromInseeCode(
            $inseeCode = $adherent->getMunicipalChiefManagedArea()->getInseeCode()
        );

        if (!$cityData) {
            throw new \InvalidArgumentException(sprintf('[MunicipalChief] City with insee code "%s" is not identified', $inseeCode));
        }

        return $cityData['postal_code'];
    }
}
