<?php

declare(strict_types=1);

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
    public const MEMBER_CARD = 'member_card';
    public const COMMUNICATION = 'communication';
    public const COMMITTEE = 'committee';

    public const LABELS = [
        self::MAIN_INFORMATION => 'Étape 1/7 Compte créé',
        self::ACTIVATION => 'Étape 2/7 Email validé',
        self::PASSWORD => 'Étape 3/7 Mot de passe créé',
        self::FURTHER_INFORMATION => 'Étape 4/7 Date de naissance, mandats et numéro de tel',
        self::MEMBER_CARD => 'Étape 5/7 Adresse confirmée',
        self::COMMUNICATION => 'Étape 6/7 Optins communication confirmés',
        self::COMMITTEE => 'Étape 7/7 Choix du comité confirmé',
    ];

    public static function all(bool $isAdherent): array
    {
        if ($isAdherent) {
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

        return [
            AdhesionController::ROUTE_NAME => self::MAIN_INFORMATION,
            ActivateEmailController::ROUTE_NAME => self::ACTIVATION,
            CreatePasswordController::ROUTE_NAME => self::PASSWORD,
            FurtherInformationController::ROUTE_NAME => self::FURTHER_INFORMATION,
            CommunicationReminderController::ROUTE_NAME => self::COMMUNICATION,
        ];
    }

    public static function getNextStep(bool $isAdherent, array $finishedSteps): ?string
    {
        return self::getNextStepInCollection(self::all($isAdherent), $finishedSteps);
    }

    public static function getLastFilledStep(bool $isAdherent, array $finishedSteps): ?string
    {
        $previousStep = null;
        foreach (self::all($isAdherent) as $step) {
            if (!\in_array($step, $finishedSteps)) {
                break;
            }

            $previousStep = $step;
        }

        return $previousStep;
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
