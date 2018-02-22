<?php

namespace AppBundle\Mailer\Message;

class MessageRegistry
{
    private $transactional = [
        AdherentAccountActivationMessage::class => 'adherent_account_activation_message',
    ];

    private $campaign = [];

    public function getMessageTemplate(string $class): string
    {
        if (\array_key_exists($class, $this->transactional)) {
            return $this->transactional[$class];
        }

        if (\array_key_exists($class, $this->campaign)) {
            return $this->campaign[$class];
        }

        throw new \Exception("Message $class does not exist.");
    }

    public function getMessageClass(string $name): string
    {
        if (\in_array($name, $this->transactional)) {
            return \array_search($name, $this->transactional);
        }

        if (\in_array($name, $this->campaign)) {
            return \array_search($name, $this->campaign);
        }

        throw new \Exception("Message with name $name does not exist.");
    }

    public function getAllMessages(): array
    {
        return \array_merge($this->transactional, $this->campaign);
    }
}
