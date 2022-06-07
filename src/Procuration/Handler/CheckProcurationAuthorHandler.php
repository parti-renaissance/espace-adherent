<?php

namespace App\Procuration\Handler;

use App\Entity\ProcurationProxy;
use App\Entity\ProcurationRequest;
use App\Procuration\Command\NewProcurationObjectCommand;
use App\Procuration\ProcurationDisableReasonEnum;
use App\Validator\InvalidEmailAddress;
use App\Validator\StrictEmail;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Validator\ConstraintViolation;
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

        $errors = $this->validator->validate($object->getEmailAddress(), [
            new InvalidEmailAddress(),
            new StrictEmail(),
        ]);

        /** @var ConstraintViolation $error */
        foreach ($errors as $error) {
            if ($error->getConstraint() instanceof InvalidEmailAddress) {
                $object->disable(ProcurationDisableReasonEnum::BANNED_EMAIL);
                break;
            } elseif ($error->getConstraint() instanceof StrictEmail) {
                $object->disable(ProcurationDisableReasonEnum::INVALID_EMAIL);
                break;
            }
        }

        if ($errors->count()) {
            $this->entityManager->flush();
        }
    }
}
