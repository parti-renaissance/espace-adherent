<?php

namespace App\Procuration\Handler;

use App\Entity\ProcurationProxy;
use App\Entity\ProcurationRequest;
use App\Procuration\Command\NewProcurationObjectCommand;
use App\Validator\InvalidEmailAddress;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CheckProcurationAuthorHandler implements MessageHandlerInterface
{
    private EntityManagerInterface $entityManager;
    private ValidatorInterface $validator;

    public function __construct(EntityManagerInterface $entityManager, ValidatorInterface $validator)
    {
        $this->entityManager = $entityManager;
        $this->validator = $validator;
    }

    public function __invoke(NewProcurationObjectCommand $command): void
    {
        /** @var ProcurationProxy|ProcurationRequest $object */
        $object = $this->entityManager->find($command->getClass(), $command->getId());

        $errors = $this->validator->validate($object->getEmailAddress(), [new InvalidEmailAddress()]);

        if ($errors->count()) {
            $object->disable('banned_email');

            $this->entityManager->flush();
        }
    }
}
