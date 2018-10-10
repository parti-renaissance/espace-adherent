<?php

namespace AppBundle\Interactive;

use AppBundle\Entity\PurchasingPowerInvitation;
use AppBundle\Mail\Transactional\PurchasingPowerMail;
use Doctrine\Common\Persistence\ObjectManager;
use EnMarche\MailerBundle\MailPost\MailPostInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Workflow\StateMachine;

final class PurchasingPowerProcessorHandler
{
    public const SESSION_KEY = 'purchasing_power';

    private $builder;
    private $manager;
    private $mailPost;
    private $stateMachine;

    public function __construct(
        PurchasingPowerMessageBodyBuilder $builder,
        ObjectManager $manager,
        MailPostInterface $mailPost,
        StateMachine $stateMachine
    ) {
        $this->builder = $builder;
        $this->manager = $manager;
        $this->mailPost = $mailPost;
        $this->stateMachine = $stateMachine;
    }

    public function start(SessionInterface $session): PurchasingPowerProcessor
    {
        return $session->get(self::SESSION_KEY, new PurchasingPowerProcessor());
    }

    public function save(SessionInterface $session, PurchasingPowerProcessor $processor): void
    {
        $session->set(self::SESSION_KEY, $processor);
    }

    public function terminate(SessionInterface $session): void
    {
        $session->remove(self::SESSION_KEY);
    }

    public function getCurrentTransition(PurchasingPowerProcessor $processor): string
    {
        return current($this->stateMachine->getEnabledTransitions($processor))->getName();
    }

    /**
     * Returns whether the process is finished or not.
     */
    public function process(SessionInterface $session, PurchasingPowerProcessor $processor): ?PurchasingPowerInvitation
    {
        if ($this->stateMachine->can($processor, PurchasingPowerProcessor::TRANSITION_SEND)) {
            // End process
            $processor->refreshChoices($this->manager); // merge objects from session before mapping them in the entity
            $purchasingPower = PurchasingPowerInvitation::createFromProcessor($processor);

            $this->manager->persist($purchasingPower);
            $this->manager->flush();

            $this->mailPost->address(
                PurchasingPowerMail::class,
                PurchasingPowerMail::createRecipient($purchasingPower),
                PurchasingPowerMail::createReplyTo($purchasingPower),
                PurchasingPowerMail::createTemplateVars($purchasingPower),
                PurchasingPowerMail::createSubject($purchasingPower),
                PurchasingPowerMail::createSender($purchasingPower),
                PurchasingPowerMail::createCcRecipients($purchasingPower)
            );

            $this->terminate($session);
            $this->stateMachine->apply($processor, PurchasingPowerProcessor::TRANSITION_SEND);

            return $purchasingPower;
        }

        // Continue processing
        $this->stateMachine->apply($processor, $this->getCurrentTransition($processor));

        if ($this->stateMachine->can($processor, PurchasingPowerProcessor::TRANSITION_SEND)) {
            $this->builder->buildMessageBody($processor);
        }

        $this->save($session, $processor);

        return null;
    }
}
