<?php

namespace App\Exception;

use Ramsey\Uuid\UuidInterface;

class CommitteeMembershipException extends \RuntimeException
{
    private $membershipUuid;

    public function __construct(UuidInterface $membershipUuid, $message = '', ?\Exception $previous = null)
    {
        parent::__construct($message, 0, $previous);

        $this->membershipUuid = $membershipUuid;
    }

    public static function createNotPromotableHostPrivilegeException(
        UuidInterface $membershipUuid,
        ?\Exception $previous = null
    ): self {
        return new self(
            $membershipUuid,
            \sprintf('Committee membership "%s" cannot be promoted to the host privilege.', $membershipUuid),
            $previous
        );
    }

    public static function createNotPromotableHostPrivilegeManyHostsException(
        UuidInterface $membershipUuid,
        ?\Exception $previous = null
    ): self {
        return new self(
            $membershipUuid,
            \sprintf('Committee membership "%s" cannot be promoted to the host privilege. Only 2 hosts per committee allowed.', $membershipUuid),
            $previous
        );
    }

    public static function createNotDemotableFollowerPrivilegeException(
        UuidInterface $membershipUuid,
        ?\Exception $previous = null
    ): self {
        return new self(
            $membershipUuid,
            \sprintf('Committee membership "%s" cannot be demoted to the simple follower.', $membershipUuid),
            $previous
        );
    }
}
