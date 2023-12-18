<?php

namespace App\Adhesion;

final class AdhesionStepEnum
{
    public const MAIN_INFORMATION = 'main_information';
    public const ACTIVATION = 'activation';
    public const PASSWORD = 'password';
    public const FURTHER_INFORMATION = 'further_information';

    public static function all(): array
    {
        return [
            'app_adhesion_index' => self::MAIN_INFORMATION,
            'app_adhesion_confirm_email' => self::ACTIVATION,
            'app_adhesion_password_create' => self::PASSWORD,
            'app_adhesion_further_information' => self::FURTHER_INFORMATION,
        ];
    }

    public static function getNextStep(array $finishedSteps): ?string
    {
        foreach (self::all() as $routeName => $step) {
            if (!\in_array($step, $finishedSteps)) {
                return $routeName;
            }
        }

        return null;
    }
}
