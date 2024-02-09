<?php

namespace App\Adhesion;

use App\Controller\Renaissance\Adhesion\ActivateEmailController;
use App\Controller\Renaissance\Adhesion\AdhesionController;
use App\Controller\Renaissance\Adhesion\CommitteeController;
use App\Controller\Renaissance\Adhesion\CommunicationReminderController;
use App\Controller\Renaissance\Adhesion\CreatePasswordController;
use App\Controller\Renaissance\Adhesion\FurtherInformationController;
use App\Controller\Renaissance\Adhesion\MemberCardController;

final class AdhesionStepEnum
{
    public const MAIN_INFORMATION = 'main_information';
    public const ACTIVATION = 'activation';
    public const PASSWORD = 'password';
    public const FURTHER_INFORMATION = 'further_information';
    public const COMMITTEE = 'committee';
    public const COMMUNICATION = 'communication';
    public const MEMBER_CARD = 'member_card';

    public static function all(): array
    {
        return [
            AdhesionController::ROUTE_NAME => self::MAIN_INFORMATION,
            ActivateEmailController::ROUTE_NAME => self::ACTIVATION,
            CreatePasswordController::ROUTE_NAME => self::PASSWORD,
            FurtherInformationController::ROUTE_NAME => self::FURTHER_INFORMATION,
            MemberCardController::ROUTE_NAME => self::MEMBER_CARD,
            CommunicationReminderController::ROUTE_NAME => self::COMMUNICATION,
            CommitteeController::ROUTE_NAME => self::COMMITTEE,
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
