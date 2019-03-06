<?php

namespace AppBundle\DataFixtures\ORM;

use AppBundle\Entity\PurchasingPowerChoice;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Ramsey\Uuid\Uuid;

class LoadPurchasingPowerData implements FixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $choices = self::createChoices();

        // Persist all the things
        foreach ($choices as $choice) {
            $manager->persist($choice);
        }

        $manager->flush();
    }

    public static function createChoices(): array
    {
        // Step 0
        $choices[] = self::createNoStepChoice(
            '28ceb6d3-ec64-4a58-99a4-71357600d07c',
            PurchasingPowerChoice::MAIL_INTRODUCTION_KEY,
            'Introduction',
            file_get_contents(__DIR__.'/../interactive/mail-introduction-purchasing-power.txt')
        );

        $choices[] = self::createNoStepChoice(
            '3d735d18-348c-4d02-8046-7976f86e5ecc',
            PurchasingPowerChoice::MAIL_CONCLUSION_KEY,
            'Conclusion',
            'La République En Marche a publié des documents plus précis à ce sujet (HYPERLIEN) et je reste pour ma part à ta disposition pour en reparler!'
        );

        $choices[] = self::createNoStepChoice(
            'a642dbc7-aba5-49e4-877a-06bc1ef23168',
            PurchasingPowerChoice::MAIL_COMMON_KEY,
            'Mesures communes',
            file_get_contents(__DIR__.'/../interactive/mail-common-purchasing-power.txt')
        );

        // Step 1
        $choices[] = self::createFirstStepChoice(
            '443b06fb-5cf2-4025-8732-84e407a420f8',
            'S01C01',
            'Salarié du secteur privé',
            file_get_contents(__DIR__.'/../interactive/mail-pp-s01c01.txt')
        );

        $choices[] = self::createFirstStepChoice(
            'aa832573-34c3-4f28-b069-9413ca5f865c',
            'S01C02',
            'Salarié de la fonction publique',
            file_get_contents(__DIR__.'/../interactive/mail-pp-s01c02.txt')
        );

        $choices[] = self::createFirstStepChoice(
            '0421b645-71c0-4d2b-88ca-21969c0e1b8a',
            'S01C03',
            'Indépendant',
            file_get_contents(__DIR__.'/../interactive/mail-pp-s01c03.txt')
        );

        $choices[] = self::createFirstStepChoice(
            '754f8ce5-3950-43d9-8f05-2ef8b8e91615',
            'S01C04',
            'Étudiant',
            file_get_contents(__DIR__.'/../interactive/mail-pp-s01c04.txt')
        );

        $choices[] = self::createFirstStepChoice(
            '79f7b99d-3795-4e43-b58c-c49d86d26804',
            'S01C05',
            'Retraité modeste',
            file_get_contents(__DIR__.'/../interactive/mail-pp-s01c05.txt')
        );

        $choices[] = self::createFirstStepChoice(
            '0c6812bf-d1ac-472b-8879-0f59a0176e2f',
            'S01C06',
            'Retraité aisé',
            file_get_contents(__DIR__.'/../interactive/mail-pp-s01c06.txt')
        );

        $choices[] = self::createFirstStepChoice(
            '3b6fbde2-058a-4e15-90c5-2bfa7359db6e',
            'S01C07',
            'Ne travaille pas',
            ''
        );

        // Step 2
        $choices[] = self::createSecondStepChoice(
            '52b738ad-c078-4952-bea5-caba65b688f6',
            'S02C01',
            'Il bénéficie peut-être de l\'allocation adulte handicapé',
            file_get_contents(__DIR__.'/../interactive/mail-pp-s02c01.txt')
        );

        $choices[] = self::createSecondStepChoice(
            '642527cd-7427-41fa-959b-ab64ab50f0f5',
            'S02C02',
            'Il est en situation de précarité énergétique',
            file_get_contents(__DIR__.'/../interactive/mail-pp-s02c02.txt')
        );

        $choices[] = self::createSecondStepChoice(
            '1d5762da-220c-4a0f-8abc-d2f5b155748d',
            'S02C03',
            'Il bénéficie peut-être du minimum vieillesse',
            file_get_contents(__DIR__.'/../interactive/mail-pp-s02c03.txt')
        );

        $choices[] = self::createSecondStepChoice(
            '04236bb1-7a00-481e-ab18-1900e8d3344c',
            'S02C04',
            'Il s\'occupe seul d\'un enfant et bénéfice peut-être du complément mode de garde',
            file_get_contents(__DIR__.'/../interactive/mail-pp-s02c04.txt')
        );

        $choices[] = self::createSecondStepChoice(
            '0ebfffcc-1ea2-476c-bd8b-b7b32efe27cf',
            'S02C05',
            'Il bénéficie de la prime d\'activité',
            file_get_contents(__DIR__.'/../interactive/mail-pp-s02c05.txt')
        );

        $choices[] = self::createSecondStepChoice(
            'eeb85893-ef5c-4e7f-ad64-a4f640a0a7ad',
            'S02C06',
            'Il souhaite changer une veille voiture',
            file_get_contents(__DIR__.'/../interactive/mail-pp-s02c06.txt')
        );

        $choices[] = self::createSecondStepChoice(
            '9b75b13c-06c9-437e-bc88-726c19ca7050',
            'S02C07',
            'Il a besoin d\'aides chez lui ou chez un proche',
            file_get_contents(__DIR__.'/../interactive/mail-pp-s02c07.txt')
        );

        $choices[] = self::createSecondStepChoice(
            '0612691b-d6c6-4ed8-8d35-fac7f00e7046',
            'S02C08',
            'Il veut créer une entreprise',
            file_get_contents(__DIR__.'/../interactive/mail-pp-s02c08.txt')
        );

        // Step 3
        $choices[] = self::createThirdStepChoice(
            '7fc4e370-1b81-47de-93d9-7e001213ceb6',
            'S03C01',
            'Le travail',
            file_get_contents(__DIR__.'/../interactive/mail-pp-s03c01.txt')
        );

        $choices[] = self::createThirdStepChoice(
            'b966e9c5-7afe-49fe-945b-780fb9439e47',
            'S03C02',
            'La solidarité',
            file_get_contents(__DIR__.'/../interactive/mail-pp-s03c02.txt')
        );

        $choices[] = self::createThirdStepChoice(
            'bcc44956-fa6c-4b32-b53f-0e843c42f2a4',
            'S03C03',
            'L\'écologie',
            file_get_contents(__DIR__.'/../interactive/mail-pp-s03c03.txt')
        );

        $choices[] = self::createThirdStepChoice(
            '9d3972e4-bb24-4754-8909-5c15ec968279',
            'S03C04',
            'La responsabilité ',
            file_get_contents(__DIR__.'/../interactive/mail-pp-s03c04.txt')
        );

        return $choices;
    }

    private static function createNoStepChoice(
        string $uuid,
        string $key,
        string $label,
        string $content
    ): PurchasingPowerChoice {
        return static::createChoice($uuid, 0, $key, $label, $content);
    }

    private static function createFirstStepChoice(
        string $uuid,
        string $key,
        string $label,
        string $content
    ): PurchasingPowerChoice {
        return static::createChoice($uuid, 1, $key, $label, $content);
    }

    private static function createSecondStepChoice(
        string $uuid,
        string $key,
        string $label,
        string $content
    ): PurchasingPowerChoice {
        return static::createChoice($uuid, 2, $key, $label, $content);
    }

    private static function createThirdStepChoice(
        string $uuid,
        string $key,
        string $label,
        string $content
    ): PurchasingPowerChoice {
        return static::createChoice($uuid, 3, $key, $label, $content);
    }

    private static function createChoice(
        string $uuid,
        int $step,
        string $key,
        string $label,
        string $content
    ): PurchasingPowerChoice {
        return new PurchasingPowerChoice(Uuid::fromString($uuid), $step, $key, $label, $content);
    }
}
