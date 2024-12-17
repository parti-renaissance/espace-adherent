<?php

namespace App\Adhesion\Handler;

use App\Address\PostAddressFactory;
use App\Adherent\Tag\TagEnum;
use App\Adhesion\AdhesionStepEnum;
use App\Adhesion\Command\CreateAccountCommand;
use App\Adhesion\CreateAdherentResult;
use App\Entity\Adherent;
use App\Membership\AdherentFactory;
use App\Membership\Event\UserEvent;
use App\Membership\MembershipNotifier;
use App\Membership\MembershipSourceEnum;
use App\Membership\UserEvents;
use App\Renaissance\Membership\Admin\MembershipTypeEnum;
use App\Repository\AdherentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

#[AsMessageHandler]
class CreateAccountCommandHandler
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly AdherentRepository $adherentRepository,
        private readonly AdherentFactory $adherentFactory,
        private readonly MembershipNotifier $membershipNotifier,
        private readonly EventDispatcherInterface $eventDispatcher,
    ) {
    }

    public function __invoke(CreateAccountCommand $command): CreateAdherentResult
    {
        $membershipRequest = $command->membershipRequest;

        /** @var Adherent $currentUser */
        if ($currentUser = $command->currentUser) {
            $currentUser->updateFromMembershipRequest($membershipRequest);
            $currentUser->finishAdhesionSteps(AdhesionStepEnum::all(true));
        } elseif ($adherent = $this->adherentRepository->findOneByEmail($membershipRequest->email)) {
            $this->membershipNotifier->sendConnexionDetailsMessage($adherent);

            return CreateAdherentResult::createAlreadyExists();
        } else {
            $currentUser = $this->adherentFactory->createFromRenaissanceMembershipRequest($membershipRequest);
            $this->entityManager->persist($currentUser);
        }

        $currentUser->setPostAddress(PostAddressFactory::createFromAddress($membershipRequest->address));
        $currentUser->setSource(MembershipSourceEnum::RENAISSANCE);
        $currentUser->utmSource = $membershipRequest->utmSource;
        $currentUser->utmCampaign = $membershipRequest->utmCampaign;

        $currentUser->partyMembership = $membershipRequest->exclusiveMembership ? MembershipTypeEnum::EXCLUSIVE : match ($membershipRequest->partyMembership) {
            1 => MembershipTypeEnum::TERRITOIRES_PROGRES,
            2 => MembershipTypeEnum::AGIR,
            3 => MembershipTypeEnum::OTHER,
        };

        $currentUser->join();
        $currentUser->setV2(true);
        $currentUser->finishAdhesionStep(AdhesionStepEnum::MAIN_INFORMATION);

        if (!$currentUser->tags) {
            $currentUser->tags = [TagEnum::SYMPATHISANT_ADHESION_INCOMPLETE];
        }

        $this->entityManager->flush();

        $this->eventDispatcher->dispatch(new UserEvent($currentUser, $membershipRequest->allowNotifications), UserEvents::USER_CREATED);

        if (!$currentUser->isEligibleForMembershipPayment()) {
            return CreateAdherentResult::createActivation()->withAdherent($currentUser);
        }

        return CreateAdherentResult::createPayment()->withAdherent($currentUser);
    }
}
