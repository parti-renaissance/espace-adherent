<?php

namespace AppBundle\Controller\EnMarche\AssessorSpace;

use ApiPlatform\Core\DataProvider\PaginatorInterface;
use AppBundle\Assessor\Filter\AssessorRequestExportFilter;
use AppBundle\Entity\Adherent;
use AppBundle\Intl\FranceCitiesBundle;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
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

    protected function getVotePlacesPaginator(int $page): PaginatorInterface
    {
        return $this->votePlaceRepository->findAllForPostalCode(
            $this->getMunicipalChiefZonePostalCode(),
            $page,
            self::PAGE_LIMIT
        );
    }

    protected function getExportFilter(): AssessorRequestExportFilter
    {
        return new AssessorRequestExportFilter([], [$this->getMunicipalChiefZonePostalCode()]);
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
