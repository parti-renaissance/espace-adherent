<?php

namespace App\Controller\EnMarche\AdherentMessage;

use App\AdherentMessage\AdherentMessageTypeEnum;
use App\Entity\Adherent;
use App\Entity\AdherentMessage\AdherentMessageInterface;
use App\Entity\TerritorialCouncil\TerritorialCouncilMembership;
use App\Repository\TerritorialCouncil\TerritorialCouncilMembershipRepository;
use App\Subscription\SubscriptionTypeEnum;
use App\TerritorialCouncil\Filter\MembersListFilter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(path="/espace-referent/messagerie-conseil-territorial", name="app_message_referent_territorial_council_")
 *
 * @Security("is_granted('ROLE_REFERENT') or (is_granted('ROLE_DELEGATED_REFERENT') and is_granted('HAS_DELEGATED_ACCESS_MESSAGES'))")
 */
class ReferentTerritorialCouncilMessageController extends AbstractMessageController
{
    private $territorialCouncilMembershipRepository;

    public function __construct(TerritorialCouncilMembershipRepository $territorialCouncilMembershipRepository)
    {
        $this->territorialCouncilMembershipRepository = $territorialCouncilMembershipRepository;
    }

    protected function getMessageType(): string
    {
        return AdherentMessageTypeEnum::REFERENT_TERRITORIAL_COUNCIL;
    }

    /**
     * @return Adherent[]
     */
    protected function getMessageRecipients(AdherentMessageInterface $message): array
    {
        $filter = $message->getFilter();

        if (!$filter) {
            throw new \InvalidArgumentException('Message does not have a filter');
        }

        $memberships = $this->territorialCouncilMembershipRepository->searchByFilter(
            new MembersListFilter([$filter->getReferentTag()], SubscriptionTypeEnum::REFERENT_EMAIL),
            1,
            null
        );

        return array_map(function (TerritorialCouncilMembership $membership): Adherent {
            return $membership->getAdherent();
        }, $memberships);
    }
}
