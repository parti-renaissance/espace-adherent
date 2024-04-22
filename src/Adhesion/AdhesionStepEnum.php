<?php

namespace App\Adhesion;

use App\Controller\BesoinDEurope\Inscription;
use App\Controller\Renaissance\Adhesion;

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
            Adhesion\AdhesionController::ROUTE_NAME => self::MAIN_INFORMATION,
            Adhesion\ActivateEmailController::ROUTE_NAME => self::ACTIVATION,
            Adhesion\CreatePasswordController::ROUTE_NAME => self::PASSWORD,
            Adhesion\FurtherInformationController::ROUTE_NAME => self::FURTHER_INFORMATION,
            Adhesion\MemberCardController::ROUTE_NAME => self::MEMBER_CARD,
            Adhesion\CommunicationReminderController::ROUTE_NAME => self::COMMUNICATION,
            Adhesion\CommitteeController::ROUTE_NAME => self::COMMITTEE,
        ];
    }

    public static function allBesoinDEurope(): array
    {
        return [
            Inscription\InscriptionController::ROUTE_NAME => self::MAIN_INFORMATION,
            Inscription\ActivateEmailController::ROUTE_NAME => self::ACTIVATION,
            Inscription\CreatePasswordController::ROUTE_NAME => self::PASSWORD,
            Inscription\FurtherInformationController::ROUTE_NAME => self::FURTHER_INFORMATION,
            Inscription\CommunicationReminderController::ROUTE_NAME => self::COMMUNICATION,
        ];
    }

    public static function getNextStep(array $finishedSteps): ?string
    {
        return self::getNextStepInCollection(self::all(), $finishedSteps);
    }

    public static function getBesoinDEuropeNextStep(array $finishedSteps): ?string
    {
        return self::getNextStepInCollection(self::allBesoinDEurope(), $finishedSteps);
    }

    private static function getNextStepInCollection(array $collection, array $finishedSteps): ?string
    {
        foreach ($collection as $routeName => $step) {
            if (!\in_array($step, $finishedSteps)) {
                return $routeName;
            }
        }

        return null;
    }
}
