<?php

declare(strict_types=1);

namespace Tests\App\Validator;

use Symfony\Component\Validator\Test\ConstraintValidatorTestCase as SymfonyConstraintValidatorTestCase;

abstract class ConstraintValidatorTestCase extends SymfonyConstraintValidatorTestCase
{
    private ?string $defaultLocale = null;

    protected function setUp(): void
    {
        $this->defaultLocale = \Locale::getDefault();

        parent::setUp();

        \Locale::setDefault($this->defaultLocale);
    }
}
