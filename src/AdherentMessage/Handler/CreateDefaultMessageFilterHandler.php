<?php

namespace AppBundle\AdherentMessage\Handler;

use AppBundle\AdherentMessage\AdherentMessageTypeEnum;
use AppBundle\AdherentMessage\Command\CreateDefaultMessageFilterCommand;
use AppBundle\AdherentMessage\Filter\FilterFactory;
use AppBundle\Entity\Adherent;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Security\Core\Security;

class CreateDefaultMessageFilterHandler implements MessageHandlerInterface
{
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public function __invoke(CreateDefaultMessageFilterCommand $command): void
    {
        $message = $command->getMessage();

        if (!\in_array($message->getType(), [AdherentMessageTypeEnum::DEPUTY])) {
            return;
        }

        $user = $this->security->getUser();

        if (!$user instanceof Adherent) {
            return;
        }

        $message->setFilter(FilterFactory::create($user, $message->getType()));
    }
}
