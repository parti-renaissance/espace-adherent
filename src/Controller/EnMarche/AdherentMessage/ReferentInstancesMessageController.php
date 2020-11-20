<?php

namespace App\Controller\EnMarche\AdherentMessage;

use App\AdherentMessage\AdherentMessageTypeEnum;
use App\Entity\Adherent;
use App\Entity\AdherentMessage\AbstractAdherentMessage;
use App\Entity\AdherentMessage\AdherentMessageInterface;
use App\Entity\AdherentMessage\Filter\ReferentInstancesFilter;
use App\Entity\TerritorialCouncil\PoliticalCommitteeFeedItem;
use App\Entity\TerritorialCouncil\TerritorialCouncilFeedItem;
use App\Repository\TerritorialCouncil\PoliticalCommitteeMembershipRepository;
use App\Repository\TerritorialCouncil\TerritorialCouncilMembershipRepository;
use App\Subscription\SubscriptionTypeEnum;
use App\TerritorialCouncil\Filter\MembersListFilter;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
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
        $this->setTemplate('send_success', 'message/send_success/referent_instances.html.twig');
    }

    /**
     * @Route("/{uuid}/publish", name="publish_message", methods={"GET"})
     *
     * @Security("is_granted('IS_AUTHOR_OF', message) and !message.isSendToTimeline()")
     */
    public function publishMessageAction(
        Request $request,
        AbstractAdherentMessage $message,
        EntityManagerInterface $manager
    ): Response {
        /** @var ReferentInstancesFilter $filter */
        $filter = $message->getFilter();

        if ($request->query->has('coterr')) {
            if (!$territorialCouncil = $filter->getTerritorialCouncil()) {
                throw new BadRequestHttpException();
            }

            $item = new TerritorialCouncilFeedItem($territorialCouncil, $message->getAuthor(), $message->getContent());
        } else {
            if (!$politicalCommittee = $filter->getPoliticalCommittee()) {
                throw new BadRequestHttpException();
            }

            $item = new PoliticalCommitteeFeedItem($politicalCommittee, $message->getAuthor(), $message->getContent());
        }

        $message->setSendToTimeline(true);
        $manager->persist($item);
        $manager->flush();

        $this->addFlash('info', 'Le message a bien été publié.');

        return $this->redirectToMessageRoute('list');
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
