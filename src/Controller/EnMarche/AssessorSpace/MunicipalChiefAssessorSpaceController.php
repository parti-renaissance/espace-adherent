<?php

namespace App\Controller\EnMarche\AssessorSpace;

use App\Assessor\Filter\AssessorRequestExportFilter;
use App\Assessor\Filter\AssociationVotePlaceFilter;
use App\Entity\Election;
use App\Form\Assessor\DefaultVotePlaceFilterType;
use App\Intl\FranceCitiesBundle;
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
        $inseeCodes = $this->getMunicipalChiefZoneInseeCodes();
        $postalCodes = [];

        foreach ($inseeCodes as $inseeCode) {
            if ($cityData = FranceCitiesBundle::getCityDataFromInseeCode($inseeCode)) {
                $postalCodes[] = $cityData['postal_code'];
            }
        }

        if (!$postalCodes) {
            throw new \InvalidArgumentException(sprintf('[MunicipalChief] City with insee code "%s" is not identified', $inseeCode));
        }

        return new AssessorRequestExportFilter([], $postalCodes);
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
        return $this->voteResultRepository->getExportQueryByInseeCodes(
            $election,
            $this->getMunicipalChiefZoneInseeCodes()
        );
    }

    private function getMunicipalChiefZoneInseeCodes(): array
    {
        $inseeCode = $this->getUser()->getMunicipalChiefManagedArea()->getInseeCode();

        if (isset(FranceCitiesBundle::SPECIAL_CITY_DISTRICTS[$inseeCode])) {
            return array_keys(FranceCitiesBundle::SPECIAL_CITY_DISTRICTS[$inseeCode]);
        }

        return [$inseeCode];
    }
}
