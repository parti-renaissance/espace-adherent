<?php

namespace App\Exception;

use Ramsey\Uuid\UuidInterface;

class CitizenProjectMembershipException extends \RuntimeException
{
    private $membershipUuid;

    public function __construct(UuidInterface $membershipUuid, $message = '', \Exception $previous = null)
    {
        parent::__construct($message, 0, $previous);

        $this->membershipUuid = $membershipUuid;
    }

    public static function createNotPromotableAdministratorPrivilegeException(
        UuidInterface $membershipUuid,
        \Exception $previous = null
    ): self {
        return new self(
            $membershipUuid,
            sprintf('Citizen project membership "%s" cannot be promoted to the administrator privilege.', $membershipUuid),
            $previous
        );
    }

    public static function createNotDemotableFollowerPrivilegeException(
        UuidInterface $membershipUuid,
        \Exception $previous = null
    ): self {
        return new self(
            $membershipUuid,
            sprintf('CitizenProject membership "%s" cannot be demoted to the simple follower.', $membershipUuid),
            $previous
        );
    }

    public function getMembershipUuid(): UuidInterface
    {
        return $this->membershipUuid;
    }
}
