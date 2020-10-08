<?php

namespace App\Controller\EnMarche\AdherentMessage;

use App\AdherentMessage\AdherentMessageTypeEnum;
use App\Entity\Adherent;
use App\Entity\AdherentMessage\AdherentMessageInterface;
use App\Entity\AdherentMessage\Filter\ReferentInstancesFilter;
use App\Repository\TerritorialCouncil\PoliticalCommitteeMembershipRepository;
use App\Repository\TerritorialCouncil\TerritorialCouncilMembershipRepository;
use App\Subscription\SubscriptionTypeEnum;
use App\TerritorialCouncil\Filter\MembersListFilter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(path="/espace-referent/messagerie-instances", name="app_message_referent_instances_")
 *
 * @Security("is_granted('ROLE_REFERENT') or (is_granted('ROLE_DELEGATED_REFERENT') and is_granted('HAS_DELEGATED_ACCESS_MESSAGES'))")
 */
class ReferentInstancesMessageController extends AbstractMessageController
{
    private $territorialCouncilMembershipRepository;
    private $politicalCommitteeMembershipRepository;

    public function __construct(
        TerritorialCouncilMembershipRepository $territorialCouncilMembershipRepository,
        PoliticalCommitteeMembershipRepository $politicalCommitteeMembershipRepository
    ) {
        $this->territorialCouncilMembershipRepository = $territorialCouncilMembershipRepository;
        $this->politicalCommitteeMembershipRepository = $politicalCommitteeMembershipRepository;

        $this->setTemplate('list', 'message/list_referent_instances.html.twig');
    }

    protected function getMessageType(): string
    {
        return AdherentMessageTypeEnum::REFERENT_INSTANCES;
    }

    /**
     * @return Adherent[]
     */
    protected function getMessageRecipients(AdherentMessageInterface $message): ?array
    {
        /** @var ReferentInstancesFilter $filter */
        $filter = $message->getFilter();

        if (!$filter) {
            throw new \InvalidArgumentException('Message does not have a filter');
        }

        $memberFilter = new MembersListFilter([], SubscriptionTypeEnum::REFERENT_EMAIL);
        $memberFilter->setTerritorialCouncil($filter->getTerritorialCouncil());
        $memberFilter->setPoliticalCommittee($filter->getPoliticalCommittee());
        $memberFilter->setQualities($filter->getQualities());

        if ($filter->getTerritorialCouncil()) {
            $memberships = $this->territorialCouncilMembershipRepository->searchByFilter($memberFilter, 1, null);
        } else {
            $memberships = $this->politicalCommitteeMembershipRepository->searchByFilter($memberFilter);
        }

        return array_map(function ($membership): Adherent {
            return $membership->getAdherent();
        }, $memberships);
    }
}
