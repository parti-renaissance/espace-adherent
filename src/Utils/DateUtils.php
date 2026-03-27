<?php

declare(strict_types=1);

namespace App\Utils;

use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

final class DateUtils
{
    public static function createValidDate(string $value): \DateTime
    {
        try {
            $date = new \DateTime($value);
        } catch (\Exception) {
            throw new BadRequestHttpException(\sprintf('Invalid date value: "%s".', $value));
        }

        $year = (int) $date->format('Y');

        if ($year < 1900 || $year > 2100) {
            throw new BadRequestHttpException(\sprintf('Invalid date value: "%s".', $value));
        }

        return $date;
    }
}
