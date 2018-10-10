<?php

namespace AppBundle\TonMacron;

use AppBundle\Entity\TonMacronFriendInvitation;
use AppBundle\Mail\Transactional\TonMacronFriendMail;
use Doctrine\Common\Persistence\ObjectManager;
use EnMarche\MailerBundle\MailPost\MailPostInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Workflow\StateMachine;

final class InvitationProcessorHandler
{
    public const SESSION_KEY = 'ton_macron.invitation';

    private $builder;
    private $manager;
    private $mailPost;
    private $stateMachine;

    public function __construct(
        TonMacronMessageBodyBuilder $builder,
        ObjectManager $manager,
        MailPostInterface $mailPost,
        StateMachine $stateMachine
    ) {
        $this->builder = $builder;
        $this->manager = $manager;
        $this->mailPost = $mailPost;
        $this->stateMachine = $stateMachine;
    }

    public function start(SessionInterface $session): InvitationProcessor
    {
        return $session->get(self::SESSION_KEY, new InvitationProcessor());
    }

    public function save(SessionInterface $session, InvitationProcessor $processor): void
    {
        $session->set(self::SESSION_KEY, $processor);
    }

    public function terminate(SessionInterface $session): void
    {
        $session->remove(self::SESSION_KEY);
    }

    public function getCurrentTransition(InvitationProcessor $processor): string
    {
        return current($this->stateMachine->getEnabledTransitions($processor))->getName();
    }

    /**
     * Returns whether the process is finished or not.
     */
    public function process(SessionInterface $session, InvitationProcessor $processor): ?TonMacronFriendInvitation
    {
        if ($this->stateMachine->can($processor, InvitationProcessor::TRANSITION_SEND)) {
            // End process
            $processor->refreshChoices($this->manager); // merge objects from session before mapping them in the entity
            $invitation = TonMacronFriendInvitation::createFromProcessor($processor);

            $this->manager->persist($invitation);
            $this->manager->flush();

            $this->mailPost->address(
                TonMacronFriendMail::class,
                TonMacronFriendMail::createRecipient($invitation),
                TonMacronFriendMail::createReplyTo($invitation),
                TonMacronFriendMail::createTemplateVars($invitation),
                TonMacronFriendMail::createSubject($invitation),
                TonMacronFriendMail::createSender($invitation),
                TonMacronFriendMail::createCcRecipients($invitation)
            );

            $this->terminate($session);
            $this->stateMachine->apply($processor, InvitationProcessor::TRANSITION_SEND);

            return $invitation;
        }

        // Continue processing
        $this->stateMachine->apply($processor, $this->getCurrentTransition($processor));

        if ($this->stateMachine->can($processor, InvitationProcessor::TRANSITION_SEND)) {
            $this->builder->buildMessageBody($processor);
        }

        $this->save($session, $processor);

        return null;
    }
}
