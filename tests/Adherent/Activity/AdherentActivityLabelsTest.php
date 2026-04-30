<?php

declare(strict_types=1);

namespace Tests\App\Adherent\Activity;

use App\Adherent\Activity\AdherentActivityLabels;
use App\History\UserActionHistoryTypeEnum;
use App\JeMengage\Hit\EventTypeEnum;
use PHPUnit\Framework\TestCase;

class AdherentActivityLabelsTest extends TestCase
{
    /**
     * @var list<UserActionHistoryTypeEnum>
     *
     * Types non exposés dans le pipeline AdherentActivity (audit/admin only)
     */
    private const array USER_ACTION_HISTORY_DENY_LIST = [
        UserActionHistoryTypeEnum::IMPERSONATION_START,
        UserActionHistoryTypeEnum::IMPERSONATION_END,
        UserActionHistoryTypeEnum::SENSITIVE_DATA_ACCESS,
    ];

    /**
     * @var list<EventTypeEnum>
     *
     * Types non exposés dans le pipeline AdherentActivity (analytique uniquement)
     */
    private const array HIT_EVENT_DENY_LIST = [
        EventTypeEnum::Impression,
    ];

    public function testEveryAllowedUserActionHistoryTypeHasLabel(): void
    {
        $deniedValues = array_map(static fn (UserActionHistoryTypeEnum $case): string => $case->value, self::USER_ACTION_HISTORY_DENY_LIST);
        $expectedKeys = array_diff(
            array_map(static fn (UserActionHistoryTypeEnum $case): string => $case->value, UserActionHistoryTypeEnum::cases()),
            $deniedValues,
        );

        $missing = array_diff($expectedKeys, AdherentActivityLabels::actionHistoryKeys());

        self::assertSame([], array_values($missing), 'Action history types missing a label in AdherentActivityLabels::ACTION_HISTORY_EVENTS');
    }

    public function testEveryActionHistoryLabelKeyIsAValidEnumValue(): void
    {
        $allEnumValues = array_map(static fn (UserActionHistoryTypeEnum $case): string => $case->value, UserActionHistoryTypeEnum::cases());

        $unknown = array_diff(AdherentActivityLabels::actionHistoryKeys(), $allEnumValues);

        self::assertSame([], array_values($unknown), 'Label keys must all match an UserActionHistoryTypeEnum value');
    }

    public function testEveryAllowedHitEventTypeHasLabel(): void
    {
        $deniedValues = array_map(static fn (EventTypeEnum $case): string => $case->value, self::HIT_EVENT_DENY_LIST);
        $expectedKeys = array_diff(
            array_map(static fn (EventTypeEnum $case): string => $case->value, EventTypeEnum::cases()),
            $deniedValues,
        );

        $missing = array_diff($expectedKeys, AdherentActivityLabels::hitEventKeys());

        self::assertSame([], array_values($missing), 'Hit event types missing a label in AdherentActivityLabels::HIT_EVENTS');
    }

    public function testEveryHitEventLabelKeyIsAValidEnumValue(): void
    {
        $allEnumValues = array_map(static fn (EventTypeEnum $case): string => $case->value, EventTypeEnum::cases());

        $unknown = array_diff(AdherentActivityLabels::hitEventKeys(), $allEnumValues);

        self::assertSame([], array_values($unknown), 'Label keys must all match an EventTypeEnum value');
    }

    public function testActionHistoryKeysReturnsArrayKeysOfActionHistoryEvents(): void
    {
        self::assertSame(
            array_keys(AdherentActivityLabels::ACTION_HISTORY_EVENTS),
            AdherentActivityLabels::actionHistoryKeys(),
        );
    }

    public function testHitEventKeysReturnsArrayKeysOfHitEvents(): void
    {
        self::assertSame(
            array_keys(AdherentActivityLabels::HIT_EVENTS),
            AdherentActivityLabels::hitEventKeys(),
        );
    }
}
