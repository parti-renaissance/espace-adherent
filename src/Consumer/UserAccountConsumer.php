<?php

namespace AppBundle\Consumer;

use AppBundle\Membership\AdherentAccountProvider;
use AppBundle\Repository\AdherentRepository;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Psr\Log\LoggerInterface;

class UserAccountConsumer implements ConsumerInterface
{
    private $provider;
    private $repository;
    private $logger;

    public function __construct(
        AdherentAccountProvider $provider,
        AdherentRepository $repository,
        LoggerInterface $logger
    ) {
        $this->provider = $provider;
        $this->repository = $repository;
        $this->logger = $logger;
    }

    public function execute(AMQPMessage $message): bool
    {
        if (false === $data = \GuzzleHttp\json_decode($message->getBody(), true)) {
            return false;
        }

        switch ($key = $message->get('routing_key')) {
            case 'user.modification':
                return $this->updateUser($key, $data);
            case 'user.deletion':
                return $this->deleteUser($key, $data);
        }

        return true;
    }

    private function updateUser(string $key, array $data): bool
    {
        if (!$dbUser = $this->repository->findByUuid($data['uuid'])) {
            $this->logger->info(
                sprintf('[%s] No Adherent (%s) to update found.', $key, $data['uuid']),
                ['data' => $data]
            );

            return true;
        }

        try {
            $dbUser->updateAccount($this->provider->getUser($data['iri']));
        } catch (\Exception $e) {
            $this->logger->error(
                sprintf('[%s] Unable to update Adherent (%s) account data.', $key, $data['uuid']),
                ['exception' => $e, 'data' => $data]
            );

            return false;
        }

        return true;
    }

    private function deleteUser(string $key, array $data): bool
    {
        return false;
    }
}
