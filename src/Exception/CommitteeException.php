<?php

namespace AppBundle\Exception;

use Ramsey\Uuid\UuidInterface;

class CommitteeException extends \RuntimeException
{
    private $committeeUuid;

    public function __construct(UuidInterface $committeeUuid, $message = '', \Exception $previous = null)
    {
        parent::__construct($message, 0, $previous);

        $this->committeeUuid = $committeeUuid;
    }

    public function getCommitteeUuid(): UuidInterface
    {
        return $this->committeeUuid;
    }
}
