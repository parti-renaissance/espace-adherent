<?php

namespace AppBundle\Controller\EnMarche\AssessorSpace;

use ApiPlatform\Core\DataProvider\PaginatorInterface;
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
        /** @var Adherent $adherent */
        $adherent = $this->getUser();

        $cityData = FranceCitiesBundle::getCityDataFromInseeCode(
            $inseeCode = $adherent->getMunicipalChiefManagedArea()->getInseeCode()
        );

        if (!$cityData) {
            throw new \InvalidArgumentException(sprintf('[MunicipalChief] City with insee code "%s" is not identified', $inseeCode));
        }

        return $this->votePlaceRepository->findAllForPostalCode(
            $cityData['postal_code'],
            $page,
            self::PAGE_LIMIT
        );
    }
}
