<?php

namespace App\Repository\OpenAI;

use App\Entity\OpenAI\Assistant;
use App\OpenAI\Model\AssistantInterface;
use App\OpenAI\Provider\AssistantProviderInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class AssistantRepository extends ServiceEntityRepository implements AssistantProviderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Assistant::class);
    }

    public function findOneByUuid(string $uuid): ?Assistant
    {
        return $this->findOneBy(['uuid' => $uuid]);
    }

    public function loadByIdentifier(string $identifier): ?AssistantInterface
    {
        return $this->findOneByUuid($identifier);
    }

    public function refresh(AssistantInterface $assistant): void
    {
        $this->_em->refresh($assistant);
    }
}
