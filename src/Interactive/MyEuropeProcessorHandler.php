<?php

namespace App\Interactive;

use App\Entity\MyEuropeInvitation;
use App\Mailer\MailerService;
use App\Mailer\Message\MyEuropeMessage;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Workflow\StateMachine;

final class MyEuropeProcessorHandler
{
    const SESSION_KEY = 'my_europe';

    private $builder;
    private $manager;
    private $mailer;
    private $stateMachine;

    public function __construct(
        MyEuropeMessageBodyBuilder $builder,
        ObjectManager $manager,
        MailerService $mailer,
        StateMachine $stateMachine
    ) {
        $this->builder = $builder;
        $this->manager = $manager;
        $this->mailer = $mailer;
        $this->stateMachine = $stateMachine;
    }

    public function start(SessionInterface $session, string $recaptcha = ''): MyEuropeProcessor
    {
        /** @var MyEuropeProcessor $myEuropeProcessor */
        $myEuropeProcessor = $session->get(self::SESSION_KEY, new MyEuropeProcessor());

        if ($recaptcha) {
            $myEuropeProcessor->setRecaptcha($recaptcha);
        }

        return $myEuropeProcessor;
    }

    public function save(SessionInterface $session, MyEuropeProcessor $processor): void
    {
        $session->set(self::SESSION_KEY, $processor);
    }

    public function terminate(SessionInterface $session): void
    {
        $session->remove(self::SESSION_KEY);
    }

    public function getCurrentTransition(MyEuropeProcessor $processor): string
    {
        return current($this->stateMachine->getEnabledTransitions($processor))->getName();
    }

    /**
     * Returns whether the process is finished or not.
     */
    public function process(SessionInterface $session, MyEuropeProcessor $processor): ?MyEuropeInvitation
    {
        if ($this->stateMachine->can($processor, MyEuropeProcessor::TRANSITION_SEND)) {
            // End process
            $processor->refreshChoices($this->manager); // merge objects from session before mapping them in the entity
            $myEurope = MyEuropeInvitation::createFromProcessor($processor);

            $this->manager->persist($myEurope);
            $this->manager->flush();

            $this->mailer->sendMessage(MyEuropeMessage::createFromInvitation($myEurope));
            $this->terminate($session);
            $this->stateMachine->apply($processor, MyEuropeProcessor::TRANSITION_SEND);

            return $myEurope;
        }

        // Continue processing
        $this->stateMachine->apply($processor, $this->getCurrentTransition($processor));

        if ($this->stateMachine->can($processor, MyEuropeProcessor::TRANSITION_SEND)) {
            $this->builder->buildMessageBody($processor);
        }

        $this->save($session, $processor);

        return null;
    }
}
