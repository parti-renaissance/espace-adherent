<?php

namespace AppBundle\Controller\EnMarche\AssessorSpace;

use ApiPlatform\Core\DataProvider\PaginatorInterface;
use AppBundle\Entity\Adherent;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
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

    protected function getVotePlacesPaginator(int $page): PaginatorInterface
    {
        /** @var Adherent $referent */
        $referent = $this->getUser();

        return $this->votePlaceRepository->findAllForTags(
            $referent->getManagedArea()->getTags()->toArray(),
            $page,
            self::PAGE_LIMIT
        );
    }
}
