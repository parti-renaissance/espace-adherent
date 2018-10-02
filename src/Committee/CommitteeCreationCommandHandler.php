<?php

namespace AppBundle\Committee;

use AppBundle\Events;
use AppBundle\Mail\Transactional\CommitteeCreationConfirmationMail;
use AppBundle\Referent\ReferentTagManager;
use EnMarche\MailerBundle\MailPost\MailPostInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class CommitteeCreationCommandHandler
{
    private $dispatcher;
    private $factory;
    private $manager;
    private $mailPost;
    private $photoManager;
    private $referentTagManager;

    public function __construct(
        EventDispatcherInterface $dispatcher,
        CommitteeFactory $factory,
        CommitteeManager $manager,
        MailPostInterface $mailPost,
        PhotoManager $photoManager,
        ReferentTagManager $referentTagManager
    ) {
        $this->dispatcher = $dispatcher;
        $this->factory = $factory;
        $this->manager = $manager;
        $this->mailPost = $mailPost;
        $this->photoManager = $photoManager;
        $this->referentTagManager = $referentTagManager;
    }

    public function handle(CommitteeCreationCommand $command): void
    {
        $adherent = $command->getAdherent();
        $committee = $this->factory->createFromCommitteeCreationCommand($command);
        // Uploads an ID photo
        $this->photoManager->addPhotoFromCommand($command, $committee);

        $this->referentTagManager->assignReferentLocalTags($committee);

        $command->setCommittee($committee);

        $this->manager->followCommittee($adherent, $committee);

        $this->dispatcher->dispatch(Events::COMMITTEE_CREATED, new CommitteeEvent($committee));

        $this->mailPost->address(
            CommitteeCreationConfirmationMail::class,
            CommitteeCreationConfirmationMail::createRecipientFromAdherent($adherent),
            null,
            CommitteeCreationConfirmationMail::createTemplateVars($adherent, $committee),
            CommitteeCreationConfirmationMail::SUBJECT
        );
    }
}
