<?php

declare(strict_types=1);

namespace Tests\App\Normalizer;

use App\Normalizer\ConstraintViolationListNormalizer;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;

class ConstraintViolationListNormalizerTest extends TestCase
{
    private ConstraintViolationListNormalizer $normalizer;

    protected function setUp(): void
    {
        $this->normalizer = new ConstraintViolationListNormalizer(new CamelCaseToSnakeCaseNameConverter());
    }

    public function testViolationWithoutCodeNorParametersStaysCompact(): void
    {
        $list = new ConstraintViolationList([
            new ConstraintViolation('Must not be blank.', null, [], 'root', 'email', null),
        ]);

        $result = $this->normalizer->normalize($list);

        self::assertSame('error', $result['status']);
        self::assertSame('Validation Failed', $result['message']);
        self::assertCount(1, $result['violations']);
        self::assertSame(['propertyPath' => 'email', 'message' => 'Must not be blank.'], $result['violations'][0]);
    }

    public function testViolationWithCodeExposesIt(): void
    {
        $list = new ConstraintViolationList([
            new ConstraintViolation('foo', null, [], 'root', 'email', null, null, 'email_typo_suggestion'),
        ]);

        $result = $this->normalizer->normalize($list);

        self::assertSame('email_typo_suggestion', $result['violations'][0]['code']);
    }

    public function testViolationWithParametersAndBusinessCodeExposesBoth(): void
    {
        $list = new ConstraintViolationList([
            new ConstraintViolation('foo', null, ['{{ suggestion }}' => 'user@gmail.com'], 'root', 'email', null, null, 'email_typo_suggestion'),
        ]);

        $result = $this->normalizer->normalize($list);

        self::assertSame('email_typo_suggestion', $result['violations'][0]['code']);
        self::assertSame(['{{ suggestion }}' => 'user@gmail.com'], $result['violations'][0]['parameters']);
    }

    public function testViolationWithUuidCodeIsHidden(): void
    {
        // c1051bb4-... = NotBlank::IS_BLANK_ERROR — Symfony built-in UUID, noise for the client.
        $list = new ConstraintViolationList([
            new ConstraintViolation('Must not be blank.', null, ['{{ value }}' => 'null'], 'root', 'name', null, null, 'c1051bb4-d103-4f74-8988-acbcafc7fdc3'),
        ]);

        $result = $this->normalizer->normalize($list);

        // Built-in UUID code is filtered out, parameters too (no business value when paired with a UUID).
        self::assertSame(
            ['propertyPath' => 'name', 'message' => 'Must not be blank.'],
            $result['violations'][0],
        );
    }

    public function testParametersWithUuidCodeAreAlsoHidden(): void
    {
        // Even if parameters are present, they ride with the code: a built-in UUID hides both.
        $list = new ConstraintViolationList([
            new ConstraintViolation('foo', null, ['{{ value }}' => 'bar'], 'root', 'email', null, null, 'c1051bb4-d103-4f74-8988-acbcafc7fdc3'),
        ]);

        $result = $this->normalizer->normalize($list);

        self::assertArrayNotHasKey('code', $result['violations'][0]);
        self::assertArrayNotHasKey('parameters', $result['violations'][0]);
    }

    public function testPropertyPathIsNormalizedToSnakeCase(): void
    {
        $list = new ConstraintViolationList([
            new ConstraintViolation('foo', null, [], 'root', 'firstName', null),
        ]);

        $result = $this->normalizer->normalize($list);

        self::assertSame('first_name', $result['violations'][0]['propertyPath']);
    }

    public function testMultipleViolationsAreAllExposed(): void
    {
        $list = new ConstraintViolationList([
            new ConstraintViolation('first error', null, [], 'root', 'email', null, null, 'code_a'),
            new ConstraintViolation('second error', null, ['{{ key }}' => 'value'], 'root', 'name', null, null, 'code_b'),
        ]);

        $result = $this->normalizer->normalize($list);

        self::assertCount(2, $result['violations']);
        self::assertSame('code_a', $result['violations'][0]['code']);
        self::assertSame('code_b', $result['violations'][1]['code']);
        self::assertSame(['{{ key }}' => 'value'], $result['violations'][1]['parameters']);
    }
}
