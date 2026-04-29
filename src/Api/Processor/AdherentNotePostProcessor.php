<?php

declare(strict_types=1);

namespace App\Api\Processor;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Adherent;
use App\Entity\Adherent\Note\AdherentNote;
use App\Entity\Adherent\Note\AdherentNoteAuthor;
use App\History\UserActionHistoryHandler;
use App\Repository\AdherentRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class AdherentNotePostProcessor implements ProcessorInterface
{
    public function __construct(
        #[Autowire(service: 'api_platform.doctrine.orm.state.persist_processor')]
        private readonly ProcessorInterface $persistProcessor,
        private readonly Security $security,
        private readonly UserActionHistoryHandler $historyHandler,
        private readonly AdherentRepository $adherentRepository,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        \assert($data instanceof AdherentNote);

        if (!isset($data->targetAdherent) && isset($uriVariables['uuid'])) {
            if (!$adherent = $this->adherentRepository->findOneBy(['uuid' => (string) $uriVariables['uuid']])) {
                throw new NotFoundHttpException('Adherent not found.');
            }
            $data->targetAdherent = $adherent;
        }

        /** @var Adherent $currentUser */
        $currentUser = $this->security->getUser();

        $noteAuthor = new AdherentNoteAuthor();
        $noteAuthor->author = $currentUser;
        $noteAuthor->type = AdherentNoteAuthor::TYPE_ADD;
        $noteAuthor->content = $data->content;
        $noteAuthor->editedAt = new \DateTimeImmutable();
        $noteAuthor->note = $data;
        $data->getAuthors()->add($noteAuthor);

        $result = $this->persistProcessor->process($data, $operation, $uriVariables, $context);

        $this->historyHandler->createAdherentNoteAdd($currentUser, $data);

        return $result;
    }
}
