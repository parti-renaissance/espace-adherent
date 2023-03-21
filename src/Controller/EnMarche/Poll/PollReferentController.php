<?php

namespace App\Controller\EnMarche\Poll;

use App\AdherentSpace\AdherentSpaceEnum;
use App\Entity\Adherent;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Security("is_granted('ROLE_REFERENT') or (is_granted('ROLE_DELEGATED_REFERENT') and is_granted('HAS_DELEGATED_ACCESS_POLLS'))")
 */
#[Route(path: '/espace-referent/question-du-jour', name: 'app_referent_polls_')]
class PollReferentController extends AbstractPollController
{
    protected function getSpaceName(): string
    {
        return AdherentSpaceEnum::REFERENT;
    }

    protected function getZones(Adherent $adherent): array
    {
        return $this->zoneRepository->findForJecouteByReferentTags($adherent->getManagedArea()->getTags()->toArray());
    }
}
