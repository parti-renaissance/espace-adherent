<?php

namespace App\Adhesion;

use App\Controller\Renaissance\Adhesion\V2\ActivateEmailController;
use App\Controller\Renaissance\Adhesion\V2\AdhesionController;
use App\Controller\Renaissance\Adhesion\V2\CommitteeController;
use App\Controller\Renaissance\Adhesion\V2\CommunicationReminderController;
use App\Controller\Renaissance\Adhesion\V2\CreatePasswordController;
use App\Controller\Renaissance\Adhesion\V2\FurtherInformationController;

final class AdhesionStepEnum
{
    public const MAIN_INFORMATION = 'main_information';
    public const ACTIVATION = 'activation';
    public const PASSWORD = 'password';
    public const FURTHER_INFORMATION = 'further_information';
    public const COMMITTEE = 'committee';
    public const COMMUNICATION = 'communication';

    public static function all(): array
    {
        return [
            AdhesionController::ROUTE_NAME => self::MAIN_INFORMATION,
            ActivateEmailController::ROUTE_NAME => self::ACTIVATION,
            CreatePasswordController::ROUTE_NAME => self::PASSWORD,
            FurtherInformationController::ROUTE_NAME => self::FURTHER_INFORMATION,
            CommitteeController::ROUTE_NAME => self::COMMITTEE,
            CommunicationReminderController::ROUTE_NAME => self::COMMUNICATION,
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
