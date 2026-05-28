<?php

declare(strict_types=1);

namespace Tests\App\Controller\Api\Signup;

use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\Response;

/**
 * Shared shape assertions for #[MapRequestPayload] error responses.
 *
 * Symfony's default error renderer emits RFC 7807 Problem Details for HttpException
 * (BadRequestHttpException → 400, validation failure → 422 with `violations`). These
 * helpers pin that contract so the three signup endpoints stay aligned with each
 * other and with the rest of the project's API surface.
 */
final class SignupApiErrorAssertions
{
    /**
     * 400 Bad Request: malformed payload (invalid JSON, missing required headers, etc.).
     * The body is the standard RFC 7807 envelope without `violations`.
     */
    public static function assertBadRequestErrorShape(Response $response): void
    {
        $body = self::decode($response);

        Assert::assertSame(Response::HTTP_BAD_REQUEST, (int) ($body['status'] ?? 0));
        Assert::assertArrayHasKey('title', $body);
        Assert::assertArrayHasKey('detail', $body);
        Assert::assertArrayHasKey('type', $body);
    }

    /**
     * 422 Unprocessable Entity: payload deserialized but a constraint failed.
     * The body contains a serialized ConstraintViolationList under `violations`.
     */
    public static function assertValidationErrorShape(Response $response): void
    {
        $body = self::decode($response);

        Assert::assertArrayHasKey('violations', $body, 'Validation failure must surface a `violations` array.');
        Assert::assertIsArray($body['violations']);
        Assert::assertNotEmpty($body['violations'], 'Validation failure must list at least one violation.');
        Assert::assertArrayHasKey('propertyPath', $body['violations'][0]);
        Assert::assertArrayHasKey('message', $body['violations'][0]);
    }

    private static function decode(Response $response): array
    {
        $body = json_decode((string) $response->getContent(), true);
        Assert::assertIsArray($body, 'Response body must be a JSON object.');

        return $body;
    }
}
