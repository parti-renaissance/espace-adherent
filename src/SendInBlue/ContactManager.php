<?php

namespace App\SendInBlue;

use App\SendInBlue\Client\ClientInterface;
use App\SendInBlue\Manager\ManagerInterface;

class ContactManager
{
    protected ClientInterface $client;

    /** @var iterable|ManagerInterface[] */
    private iterable $managers;

    public function __construct(ClientInterface $client, iterable $managers)
    {
        $this->client = $client;
        $this->managers = $managers;
    }

    public function synchronize(ContactInterface $contact, string $identifier): void
    {
        $manager = $this->getManager($contact);

        $this->client->synchronize(
            $identifier,
            $manager->getListId(),
            $manager->getAttributes($contact)
        );
    }

    public function delete(ContactInterface $contact): void
    {
        $manager = $this->getManager($contact);

        $this->client->delete($manager->getIdentifier($contact));
    }

    private function getManager(ContactInterface $contact): ManagerInterface
    {
        foreach ($this->managers as $manager) {
            if ($manager->supports($contact)) {
                return $manager;
            }
        }

        throw new \InvalidArgumentException('Unhandled');
    }
}
