<?php

namespace App\JeMengage\Timeline\Handler;

use Algolia\SearchBundle\SearchService;
use App\Entity\Adherent;
use App\Entity\AuthorInstanceInterface;
use App\JeMengage\Timeline\Command\IndexAllItemsForAuthorCommand;
use App\JeMengage\Timeline\TimelineFeedTypeEnum;
use App\Repository\AdherentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class IndexAllItemsForAuthorCommandHandler implements MessageHandlerInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly SearchService $searchService,
    ) {
    }

    public function __invoke(IndexAllItemsForAuthorCommand $command): void
    {
        /** @var AdherentRepository $adherentRepository */
        $adherentRepository = $this->entityManager->getRepository(Adherent::class);
        if (!$adherent = $adherentRepository->findByUuid($command->getUuid()->toString())) {
            return;
        }

        $this->entityManager->refresh($adherent);

        foreach (TimelineFeedTypeEnum::CLASS_MAPPING as $class => $type) {
            if (!is_a($class, AuthorInstanceInterface::class, true)) {
                continue;
            }

            $repository = $this->entityManager->getRepository($class);

            $this->searchService->index(
                $this->entityManager,
                $repository->findBy(['author' => $adherent])
            );
        }
    }
}
