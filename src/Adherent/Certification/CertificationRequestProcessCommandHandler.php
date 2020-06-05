<?php

namespace App\Adherent\Certification;

use App\Adherent\Certification\Handlers\CertificationRequestHandlerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class CertificationRequestProcessCommandHandler implements MessageHandlerInterface
{
    /**
     * @var CertificationRequestHandlerInterface[]|iterable
     */
    private $handlers;

    public function __construct(iterable $handlers)
    {
        $this->handlers = $handlers;
    }

    public function __invoke(CertificationRequestProcessCommand $command): void
    {
    }
}
