<?php

declare(strict_types=1);

namespace Tests\App\Controller\Api\Signup;

use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\Response;

/**
 * Shared shape assertions for the signup endpoints' error responses.
 *
 * The two #[MapRequestPayload] endpoints (/signup/activate, /signup/resend-code) rely on
 * Symfony's default error renderer, which emits RFC 7807 Problem Details for HttpException
 * (BadRequestHttpException → 400, validation failure → 422 with `violations`). The main
 * /signup endpoint does NOT use #[MapRequestPayload] (it injects the captcha key between
 * deserialization and validation) and therefore keeps a distinct legacy shape — see
 * assertLegacyValidationErrorShape(). These helpers pin both contracts so the divergence
 * stays intentional and visible.
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

    /**
     * /signup legacy validation shape: HTTP 400 with a serialized ConstraintViolationList whose
     * top-level `status` is the literal string "error" (NOT the RFC 7807 integer status), plus a
     * `violations` array. Distinct on purpose from the #[MapRequestPayload] 422 envelope above.
     */
    public static function assertLegacyValidationErrorShape(Response $response): void
    {
        $body = self::decode($response);

        Assert::assertSame('error', $body['status'] ?? null, 'Legacy /signup errors expose a string `status: "error"`.');
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
