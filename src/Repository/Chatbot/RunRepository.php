<?php

namespace App\Repository\Chatbot;

use App\Entity\Chatbot\Run;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class RunRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Run::class);
    }

    public function findOneByOpenAiId(string $openAiId): ?Run
    {
        return $this->findOneBy(['openAiId' => $openAiId]);
    }

    public function save(Run $run): void
    {
        $this->_em->persist($run);
        $this->_em->flush();
    }
}
