<?php

namespace App\Controller\EnMarche\AssessorSpace;

use App\AdherentSpace\AdherentSpaceEnum;
use App\Assessor\Filter\AssessorRequestExportFilter;
use App\Assessor\Filter\AssociationVotePlaceFilter;
use App\Entity\Election;
use App\Form\Assessor\DefaultVotePlaceFilterType;
use App\FranceCities\FranceCities;
use App\Intl\FranceCitiesBundle;
use App\Repository\Election\VotePlaceResultRepository;
use App\Repository\VotePlaceRepository;
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
    private FranceCities $franceCities;

    public function __construct(
        VotePlaceRepository $votePlaceRepository,
        VotePlaceResultRepository $voteResultRepository,
        bool $enableAssessorSpace,
        FranceCities $franceCities
    ) {
        $this->franceCities = $franceCities;

        parent::__construct($votePlaceRepository, $voteResultRepository, $enableAssessorSpace);
    }

    protected function getSpaceType(): string
    {
        return AdherentSpaceEnum::MUNICIPAL_CHIEF;
    }

    protected function getAssessorRequestExportFilter(): AssessorRequestExportFilter
    {
        $inseeCodes = $this->getMunicipalChiefZoneInseeCodes();
        $postalCodes = [];

        foreach ($inseeCodes as $inseeCode) {
            if ($city = $this->franceCities->getCityByInseeCode($inseeCode)) {
                $postalCodes = array_merge($postalCodes, $city->getPostalCode());
            }
        }

        if (!$postalCodes) {
            throw new \InvalidArgumentException(sprintf('[MunicipalChief] City with insee code "%s" is not identified', implode(',', $inseeCodes)));
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
