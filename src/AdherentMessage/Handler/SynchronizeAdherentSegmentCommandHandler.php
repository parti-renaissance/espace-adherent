<?php

namespace AppBundle\AdherentMessage\Handler;

use AppBundle\AdherentMessage\Command\SynchronizeAdherentSegmentCommand;
use AppBundle\Entity\AdherentSegment;
use AppBundle\Mailchimp\Exception\StaticSegmentIdMissingException;
use AppBundle\Mailchimp\Manager;
use AppBundle\Repository\AdherentRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class SynchronizeAdherentSegmentCommandHandler implements MessageHandlerInterface
{
    private $entityManager;
    private $manager;
    private $repository;

    public function __construct(ObjectManager $entityManager, Manager $manager, AdherentRepository $repository)
    {
        $this->entityManager = $entityManager;
        $this->manager = $manager;
        $this->repository = $repository;
    }

    public function __invoke(SynchronizeAdherentSegmentCommand $command): void
    {
        /** @var AdherentSegment|null $segment */
        $segment = $this->entityManager->find(AdherentSegment::class, $command->getAdherentSegmentId());

        if (!$segment) {
            return;
        }

        $this->entityManager->refresh($segment);

        if ($segment->isSynchronized()) {
            return;
        }

        if (!$segment->getMailchimpId()) {
            throw new StaticSegmentIdMissingException(sprintf('AdherentSegment "%s" does not have Mailchimp static segment id', $segment->getUuid()->toString()));
        }

        $memberEmails = $this->findMemberEmailsForSegment($segment);

        foreach ($memberEmails as $email) {
            $this->manager->addMemberToStaticSegment($segment->getMailchimpId(), $email);
        }

        $segment->setSynchronized(true);

        $this->entityManager->flush();
        $this->entityManager->clear();
    }

    private function findMemberEmailsForSegment(AdherentSegment $segment): array
    {
        return array_column(
            $this->repository->createQueryBuilder('adherent')
                ->select('adherent.emailAddress')
                ->where('adherent.id IN (:ids)')
                ->setParameter('ids', $segment->getMemberIds())
                ->getQuery()
                ->getArrayResult(),
            'emailAddress'
        );
    }
}
