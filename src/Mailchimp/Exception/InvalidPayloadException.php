<?php

declare(strict_types=1);

namespace App\Mailchimp\Exception;

/**
 * Thrown when the Mailchimp BETA API rejects the payload structure.
 * This triggers a fallback to the legacy /members endpoint.
 */
final class InvalidPayloadException extends \RuntimeException
{
}
