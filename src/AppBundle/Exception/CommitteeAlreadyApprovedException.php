<?php

namespace AppBundle\Exception;

use Ramsey\Uuid\UuidInterface;

class CommitteeAlreadyApprovedException extends \RuntimeException
{
    private $committeeUuid;

    public function __construct(UuidInterface $committeeUuid, $message = '', \Exception $previous = null)
    {
        $message = sprintf('Committee %s has already been approved by an administrator.', $committeeUuid->toString());

        parent::__construct($message, 0, $previous);

        $this->committeeUuid = $committeeUuid;
    }

    public function getAdherentUuid(): UuidInterface
    {
        return $this->committeeUuid;
    }
}
