<?php

namespace AppBundle\Interactive;

use AppBundle\Entity\PurchasingPowerInvitation;
use AppBundle\Mailjet\MailjetService;
use AppBundle\Mailjet\Message\PurchasingPowerMessage;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Workflow\StateMachine;

final class PurchasingPowerProcessorHandler
{
    const SESSION_KEY = 'purchasing_power';

    private $builder;
    private $manager;
    private $mailjet;
    private $stateMachine;

    public function __construct(
        PurchasingPowerMessageBodyBuilder $builder,
        ObjectManager $manager,
        MailjetService $mailjet,
        StateMachine $stateMachine
    ) {
        $this->builder = $builder;
        $this->manager = $manager;
        $this->mailjet = $mailjet;
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

            $this->mailjet->sendMessage(PurchasingPowerMessage::createFromInvitation($purchasingPower));
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
