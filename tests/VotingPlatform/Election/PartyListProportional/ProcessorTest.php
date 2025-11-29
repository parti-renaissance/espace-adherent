<?php

declare(strict_types=1);

namespace Tests\App\VotingPlatform\Election\PartyListProportional;

use App\VotingPlatform\Election\PartyListProportional\Model\Election;
use App\VotingPlatform\Election\PartyListProportional\Model\PartyList;
use App\VotingPlatform\Election\PartyListProportional\Processor;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ProcessorTest extends TestCase
{
    #[DataProvider('getElectionsData')]
    public function testProcessor(int $nbSeats, array $lists, array $expected): void
    {
        $partyLists = [];
        foreach ($lists as $key => $totalVotes) {
            $partyLists[] = new PartyList($key, $totalVotes);
        }
        $election = new Election($nbSeats, $partyLists);

        Processor::process($election);

        self::assertSame(
            $expected,
            array_merge(...array_map(function (PartyList $list) {
                return [$list->identifier => $list->getSeats()];
            }, $election->partyLists))
        );
    }

    public static function getElectionsData(): \Generator
    {
        yield 'Norway election 2021' => [
            8,
            ['K' => 38, 'R / RV / FMS' => 7273, 'SV / SF' => 9620, 'Ap' => 38611, 'MDG' => 3138, 'Sp / Bp / L' => 28465, 'V' => 3342, 'KrF' => 2637, 'H' => 20532, 'FrP / ALP' => 16338],
            ['K' => 0, 'R / RV / FMS' => 0, 'SV / SF' => 1, 'Ap' => 3, 'MDG' => 0, 'Sp / Bp / L' => 2, 'V' => 0, 'KrF' => 0, 'H' => 1, 'FrP / ALP' => 1],
        ];

        yield 'Norway election 2017' => [
            8,
            ['K' => 59, 'R / RV / FMS' => 3905, 'SV / SF' => 9467, 'Ap' => 35196, 'MDG' => 2932, 'Sp / Bp / L' => 25207, 'V' => 3509, 'KrF' => 3284, 'H' => 27273, 'FrP / ALP' => 22248],
            ['K' => 0, 'R / RV / FMS' => 0, 'SV / SF' => 0, 'Ap' => 2, 'MDG' => 0, 'Sp / Bp / L' => 2, 'V' => 0, 'KrF' => 0, 'H' => 2, 'FrP / ALP' => 2],
        ];

        yield 'Norway election 2013' => [
            8,
            ['K' => 58, 'R / RV / FMS' => 2164, 'SV / SF' => 6907, 'Ap' => 46743, 'MDG' => 2653, 'Sp / Bp / L' => 9237, 'V' => 4938, 'KrF' => 4886, 'H' => 28271, 'FrP / ALP' => 25020],
            ['K' => 0, 'R / RV / FMS' => 0, 'SV / SF' => 0, 'Ap' => 4, 'MDG' => 0, 'Sp / Bp / L' => 0, 'V' => 0, 'KrF' => 0, 'H' => 2, 'FrP / ALP' => 2],
        ];

        yield 'Norway election 2009' => [
            9,
            ['K' => 66, 'R / RV / FMS' => 1829, 'SV / SF' => 10045, 'Ap' => 50912, 'MDG' => 343, 'Sp / Bp / L' => 10736, 'V' => 2957, 'KrF' => 4778, 'H' => 14905, 'FrP / ALP' => 31562],
            ['K' => 0, 'R / RV / FMS' => 0, 'SV / SF' => 1, 'Ap' => 4, 'MDG' => 0, 'Sp / Bp / L' => 1, 'V' => 0, 'KrF' => 0, 'H' => 1, 'FrP / ALP' => 2],
        ];

        yield 'Norway election 2005' => [
            9,
            ['K' => 85, 'R / RV / FMS' => 1234, 'SV / SF' => 14881, 'Ap' => 48097, 'MDG' => 134, 'Sp / Bp / L' => 12063, 'V' => 4318, 'KrF' => 5917, 'H' => 11108, 'FrP / ALP' => 30017],
            ['K' => 0, 'R / RV / FMS' => 0, 'SV / SF' => 1, 'Ap' => 4, 'MDG' => 0, 'Sp / Bp / L' => 1, 'V' => 0, 'KrF' => 0, 'H' => 1, 'FrP / ALP' => 2],
        ];

        yield 'Norway election 2001' => [
            11,
            ['K' => 125, 'R / RV / FMS' => 1098, 'SV / SF' => 18188, 'Ap' => 31283, 'MDG' => 0, 'Sp / Bp / L' => 11363, 'V' => 2822, 'KrF' => 14316, 'H' => 16862, 'FrP / ALP' => 17104],
            ['K' => 0, 'R / RV / FMS' => 0, 'SV / SF' => 2, 'Ap' => 3, 'MDG' => 0, 'Sp / Bp / L' => 1, 'V' => 0, 'KrF' => 1, 'H' => 2, 'FrP / ALP' => 2],
        ];

        yield 'Norway election 1997' => [
            11,
            ['K' => 124, 'R / RV / FMS' => 1824, 'SV / SF' => 9915, 'Ap' => 48921, 'MDG' => 208, 'Sp / Bp / L' => 14752, 'V' => 4888, 'KrF' => 17732, 'H' => 12375, 'FrP / ALP' => 15377],
            ['K' => 0, 'R / RV / FMS' => 0, 'SV / SF' => 1, 'Ap' => 5, 'MDG' => 0, 'Sp / Bp / L' => 1, 'V' => 0, 'KrF' => 2, 'H' => 1, 'FrP / ALP' => 1],
        ];

        yield 'Norway election 1993' => [
            12,
            ['K' => 0, 'R / RV / FMS' => 814, 'SV / SF' => 15952, 'Ap' => 47402, 'MDG' => 110, 'Sp / Bp / L' => 30873, 'V' => 5052, 'KrF' => 7714, 'H' => 14474, 'FrP / ALP' => 4942],
            ['K' => 0, 'R / RV / FMS' => 0, 'SV / SF' => 2, 'Ap' => 5, 'MDG' => 0, 'Sp / Bp / L' => 3, 'V' => 0, 'KrF' => 1, 'H' => 1, 'FrP / ALP' => 0],
        ];

        yield 'Norway election 1989' => [
            12,
            ['K' => 0, 'R / RV / FMS' => 1623, 'SV / SF' => 21211, 'Ap' => 55457, 'MDG' => 0, 'Sp / Bp / L' => 8997, 'V' => 3837, 'KrF' => 9482, 'H' => 26715, 'FrP / ALP' => 14159],
            ['K' => 0, 'R / RV / FMS' => 0, 'SV / SF' => 2, 'Ap' => 5, 'MDG' => 0, 'Sp / Bp / L' => 1, 'V' => 0, 'KrF' => 1, 'H' => 2, 'FrP / ALP' => 1],
        ];

        yield 'Norway election 1985' => [
            12,
            ['K' => 272, 'R / RV / FMS' => 677, 'SV / SF' => 17659, 'Ap' => 66987, 'MDG' => 0, 'Sp / Bp / L' => 8853, 'V' => 3814, 'KrF' => 9094, 'H' => 35688, 'FrP / ALP' => 2421],
            ['K' => 0, 'R / RV / FMS' => 0, 'SV / SF' => 1, 'Ap' => 6, 'MDG' => 0, 'Sp / Bp / L' => 1, 'V' => 0, 'KrF' => 1, 'H' => 3, 'FrP / ALP' => 0],
        ];

        yield 'Norway election 1981' => [
            12,
            ['K' => 473, 'R / RV / FMS' => 1073, 'SV / SF' => 10997, 'Ap' => 56478, 'MDG' => 0, 'Sp / Bp / L' => 9762, 'V' => 5992, 'KrF' => 11224, 'H' => 37676, 'FrP / ALP' => 3628],
            ['K' => 0, 'R / RV / FMS' => 0, 'SV / SF' => 1, 'Ap' => 5, 'MDG' => 0, 'Sp / Bp / L' => 1, 'V' => 0, 'KrF' => 1, 'H' => 4, 'FrP / ALP' => 0],
        ];

        yield 'Norway election 1977' => [
            12,
            ['K' => 702, 'R / RV / FMS' => 882, 'SV / SF' => 6727, 'Ap' => 58178, 'MDG' => 0, 'Sp / Bp / L' => 12373, 'V' => 4742, 'KrF' => 15144, 'H' => 23447, 'FrP / ALP' => 1995],
            ['K' => 0, 'R / RV / FMS' => 0, 'SV / SF' => 1, 'Ap' => 6, 'MDG' => 0, 'Sp / Bp / L' => 1, 'V' => 0, 'KrF' => 2, 'H' => 2, 'FrP / ALP' => 0],
        ];

        yield 'Norway election 1973' => [
            12,
            ['K' => 0, 'R / RV / FMS' => 642, 'SV / SF' => 16532, 'Ap' => 42073, 'MDG' => 0, 'Sp / Bp / L' => 18027, 'V' => 5588, 'KrF' => 15040, 'H' => 12878, 'FrP / ALP' => 4830],
            ['K' => 0, 'R / RV / FMS' => 0, 'SV / SF' => 2, 'Ap' => 5, 'MDG' => 0, 'Sp / Bp / L' => 2, 'V' => 0, 'KrF' => 2, 'H' => 1, 'FrP / ALP' => 0],
        ];

        yield 'Norway election 1969' => [
            12,
            ['K' => 1327, 'R / RV / FMS' => 0, 'SV / SF' => 6379, 'Ap' => 64033, 'MDG' => 0, 'Sp / Bp / L' => 15455, 'V' => 7856, 'KrF' => 9346, 'H' => 15555, 'FrP / ALP' => 0],
            ['K' => 0, 'R / RV / FMS' => 0, 'SV / SF' => 0, 'Ap' => 6, 'MDG' => 0, 'Sp / Bp / L' => 2, 'V' => 1, 'KrF' => 1, 'H' => 2, 'FrP / ALP' => 0],
        ];

        yield 'Norway election 1965' => [
            12,
            ['K' => 1447, 'R / RV / FMS' => 0, 'SV / SF' => 10178, 'Ap' => 53185, 'MDG' => 0, 'Sp / Bp / L' => 12724, 'V' => 8629, 'KrF' => 10553, 'H' => 18199, 'FrP / ALP' => 0],
            ['K' => 0, 'R / RV / FMS' => 0, 'SV / SF' => 1, 'Ap' => 6, 'MDG' => 0, 'Sp / Bp / L' => 1, 'V' => 1, 'KrF' => 1, 'H' => 2, 'FrP / ALP' => 0],
        ];

        yield 'Norway election 1961' => [
            12,
            ['K' => 2266, 'R / RV / FMS' => 0, 'SV / SF' => 7116, 'Ap' => 49675, 'MDG' => 0, 'Sp / Bp / L' => 10318, 'V' => 5307, 'KrF' => 11659, 'H' => 14904, 'FrP / ALP' => 0],
            ['K' => 0, 'R / RV / FMS' => 0, 'SV / SF' => 1, 'Ap' => 6, 'MDG' => 0, 'Sp / Bp / L' => 1, 'V' => 0, 'KrF' => 2, 'H' => 2, 'FrP / ALP' => 0],
        ];

        yield 'Norway election 1957' => [
            12,
            ['K' => 3609, 'R / RV / FMS' => 0, 'SV / SF' => 0, 'Ap' => 52486, 'MDG' => 0, 'Sp / Bp / L' => 9587, 'V' => 6227, 'KrF' => 10445, 'H' => 14612, 'FrP / ALP' => 0],
            ['K' => 0, 'R / RV / FMS' => 0, 'SV / SF' => 0, 'Ap' => 7, 'MDG' => 0, 'Sp / Bp / L' => 1, 'V' => 1, 'KrF' => 1, 'H' => 2, 'FrP / ALP' => 0],
        ];

        yield 'Norway election 1953' => [
            12,
            ['K' => 5527, 'R / RV / FMS' => 0, 'SV / SF' => 0, 'Ap' => 52062, 'MDG' => 0, 'Sp / Bp / L' => 6594, 'V' => 7405, 'KrF' => 10411, 'H' => 13721, 'FrP / ALP' => 0],
            ['K' => 0, 'R / RV / FMS' => 0, 'SV / SF' => 0, 'Ap' => 7, 'MDG' => 0, 'Sp / Bp / L' => 1, 'V' => 1, 'KrF' => 1, 'H' => 2, 'FrP / ALP' => 0],
        ];

        yield 'Norway election Østfold' => [
            8,
            ['Ap' => 3060, 'H' => 1870, 'Sp' => 1420, 'Frp' => 1280, 'SV' => 590, 'R' => 450, 'V' => 280, 'MDG' => 290, 'KrF' => 330, 'PF' => 0],
            ['Ap' => 3, 'H' => 2, 'Sp' => 2, 'Frp' => 1, 'SV' => 0, 'R' => 0, 'V' => 0, 'MDG' => 0, 'KrF' => 0, 'PF' => 0],
        ];

        yield 'Norway election Akershus' => [
            18,
            ['Ap' => 2600, 'H' => 2770, 'Sp' => 890, 'Frp' => 1060, 'SV' => 660, 'R' => 390, 'V' => 660, 'MDG' => 459, 'KrF' => 210, 'PF' => 0],
            ['Ap' => 5, 'H' => 5, 'Sp' => 2, 'Frp' => 2, 'SV' => 1, 'R' => 1, 'V' => 1, 'MDG' => 1, 'KrF' => 0, 'PF' => 0],
        ];

        yield 'Norway election Oslo' => [
            19,
            ['Ap' => 2300, 'H' => 2360, 'Sp' => 310, 'Frp' => 600, 'SV' => 1330, 'R' => 830, 'V' => 1000, 'MDG' => 850, 'KrF' => 180, 'PF' => 0],
            ['Ap' => 4, 'H' => 5, 'Sp' => 0, 'Frp' => 1, 'SV' => 3, 'R' => 2, 'V' => 2, 'MDG' => 2, 'KrF' => 0, 'PF' => 0],
        ];

        yield 'Norway election Hedmark' => [
            6,
            ['Ap' => 3329, 'H' => 1060, 'Sp' => 2830, 'Frp' => 850, 'SV' => 670, 'R' => 330, 'V' => 220, 'MDG' => 200, 'KrF' => 160, 'PF' => 0],
            ['Ap' => 3, 'H' => 1, 'Sp' => 2, 'Frp' => 0, 'SV' => 0, 'R' => 0, 'V' => 0, 'MDG' => 0, 'KrF' => 0, 'PF' => 0],
        ];

        yield 'Norway election Oppland' => [
            5,
            ['Ap' => 3520, 'H' => 1250, 'Sp' => 2620, 'Frp' => 860, 'SV' => 530, 'R' => 370, 'V' => 229, 'MDG' => 220, 'KrF' => 160, 'PF' => 0],
            ['Ap' => 2, 'H' => 1, 'Sp' => 2, 'Frp' => 0, 'SV' => 0, 'R' => 0, 'V' => 0, 'MDG' => 0, 'KrF' => 0, 'PF' => 0],
        ];

        yield 'Norway election Buskerud' => [
            7,
            ['Ap' => 2850, 'H' => 2210, 'Sp' => 1620, 'Frp' => 1230, 'SV' => 550, 'R' => 340, 'V' => 350, 'MDG' => 290, 'KrF' => 229, 'PF' => 0],
            ['Ap' => 3, 'H' => 2, 'Sp' => 1, 'Frp' => 1, 'SV' => 0, 'R' => 0, 'V' => 0, 'MDG' => 0, 'KrF' => 0, 'PF' => 0],
        ];

        yield 'Norway election Vestfold' => [
            6,
            ['Ap' => 2700, 'H' => 2520, 'Sp' => 1000, 'Frp' => 1250, 'SV' => 600, 'R' => 440, 'V' => 400, 'MDG' => 380, 'KrF' => 350, 'PF' => 0],
            ['Ap' => 2, 'H' => 2, 'Sp' => 1, 'Frp' => 1, 'SV' => 0, 'R' => 0, 'V' => 0, 'MDG' => 0, 'KrF' => 0, 'PF' => 0],
        ];

        yield 'Norway election Telemark' => [
            5,
            ['Ap' => 3100, 'H' => 1570, 'Sp' => 1660, 'Frp' => 1280, 'SV' => 590, 'R' => 459, 'V' => 220, 'MDG' => 270, 'KrF' => 450, 'PF' => 0],
            ['Ap' => 2, 'H' => 1, 'Sp' => 1, 'Frp' => 1, 'SV' => 0, 'R' => 0, 'V' => 0, 'MDG' => 0, 'KrF' => 0, 'PF' => 0],
        ];

        yield 'Norway election Aust-Agder' => [
            3,
            ['Ap' => 2460, 'H' => 2030, 'Sp' => 1360, 'Frp' => 1340, 'SV' => 540, 'R' => 370, 'V' => 310, 'MDG' => 290, 'KrF' => 880, 'PF' => 0],
            ['Ap' => 1, 'H' => 1, 'Sp' => 1, 'Frp' => 0, 'SV' => 0, 'R' => 0, 'V' => 0, 'MDG' => 0, 'KrF' => 0, 'PF' => 0],
        ];

        yield 'Norway election Vest-Agder' => [
            5,
            ['Ap' => 2080, 'H' => 2140, 'Sp' => 1040, 'Frp' => 1330, 'SV' => 520, 'R' => 320, 'V' => 350, 'MDG' => 300, 'KrF' => 1390, 'PF' => 0],
            ['Ap' => 1, 'H' => 1, 'Sp' => 1, 'Frp' => 1, 'SV' => 0, 'R' => 0, 'V' => 0, 'MDG' => 0, 'KrF' => 1, 'PF' => 0],
        ];

        yield 'Norway election Rogaland' => [
            13,
            ['Ap' => 2240, 'H' => 2400, 'Sp' => 1050, 'Frp' => 1689, 'SV' => 490, 'R' => 370, 'V' => 340, 'MDG' => 240, 'KrF' => 810, 'PF' => 0],
            ['Ap' => 3, 'H' => 4, 'Sp' => 2, 'Frp' => 2, 'SV' => 1, 'R' => 0, 'V' => 0, 'MDG' => 0, 'KrF' => 1, 'PF' => 0],
        ];

        yield 'Norway election Hordaland' => [
            15,
            ['Ap' => 2280, 'H' => 2460, 'Sp' => 990, 'Frp' => 1270, 'SV' => 880, 'R' => 459, 'V' => 420, 'MDG' => 380, 'KrF' => 490, 'PF' => 0],
            ['Ap' => 4, 'H' => 4, 'Sp' => 2, 'Frp' => 2, 'SV' => 1, 'R' => 1, 'V' => 0, 'MDG' => 0, 'KrF' => 1, 'PF' => 0],
        ];

        yield 'Norway election Sogn og Fjordane' => [
            3,
            ['Ap' => 2650, 'H' => 1390, 'Sp' => 2870, 'Frp' => 940, 'SV' => 560, 'R' => 400, 'V' => 330, 'MDG' => 229, 'KrF' => 390, 'PF' => 0],
            ['Ap' => 1, 'H' => 1, 'Sp' => 1, 'Frp' => 0, 'SV' => 0, 'R' => 0, 'V' => 0, 'MDG' => 0, 'KrF' => 0, 'PF' => 0],
        ];

        yield 'Norway election Møre og Romsdal' => [
            7,
            ['Ap' => 2020, 'H' => 1630, 'Sp' => 1760, 'Frp' => 2230, 'SV' => 610, 'R' => 330, 'V' => 280, 'MDG' => 229, 'KrF' => 540, 'PF' => 0],
            ['Ap' => 2, 'H' => 1, 'Sp' => 2, 'Frp' => 2, 'SV' => 0, 'R' => 0, 'V' => 0, 'MDG' => 0, 'KrF' => 0, 'PF' => 0],
        ];

        yield 'Norway election Sør-Trøndelag' => [
            9,
            ['Ap' => 3000, 'H' => 1650, 'Sp' => 1510, 'Frp' => 869, 'SV' => 900, 'R' => 560, 'V' => 430, 'MDG' => 470, 'KrF' => 220, 'PF' => 0],
            ['Ap' => 3, 'H' => 2, 'Sp' => 2, 'Frp' => 1, 'SV' => 1, 'R' => 0, 'V' => 0, 'MDG' => 0, 'KrF' => 0, 'PF' => 0],
        ];

        yield 'Norway election Nord-Trøndelag' => [
            4,
            ['Ap' => 3370, 'H' => 1060, 'Sp' => 2910, 'Frp' => 810, 'SV' => 550, 'R' => 390, 'V' => 200, 'MDG' => 170, 'KrF' => 229, 'PF' => 0],
            ['Ap' => 2, 'H' => 0, 'Sp' => 2, 'Frp' => 0, 'SV' => 0, 'R' => 0, 'V' => 0, 'MDG' => 0, 'KrF' => 0, 'PF' => 0],
        ];

        yield 'Norway election Troms' => [
            5,
            ['Ap' => 2720, 'H' => 1370, 'Sp' => 1910, 'Frp' => 1410, 'SV' => 1060, 'R' => 470, 'V' => 240, 'MDG' => 290, 'KrF' => 220, 'PF' => 0],
            ['Ap' => 2, 'H' => 1, 'Sp' => 1, 'Frp' => 1, 'SV' => 0, 'R' => 0, 'V' => 0, 'MDG' => 0, 'KrF' => 0, 'PF' => 0],
        ];

        yield 'Norway election Finnmark' => [
            4,
            ['Ap' => 3160, 'H' => 680, 'Sp' => 1830, 'Frp' => 1090, 'SV' => 590, 'R' => 490, 'V' => 140, 'MDG' => 220, 'KrF' => 160, 'PF' => 1270],
            ['Ap' => 2, 'H' => 0, 'Sp' => 1, 'Frp' => 0, 'SV' => 0, 'R' => 0, 'V' => 0, 'MDG' => 0, 'KrF' => 0, 'PF' => 1],
        ];

        yield 'Limit testing Exemple 1' => [
            5,
            ['A' => 60, 'B' => 30, 'C' => 15],
            ['A' => 3, 'B' => 1, 'C' => 1],
        ];

        yield 'Limit testing Exemple 2' => [
            5,
            ['A' => 60, 'B' => 31, 'C' => 14],
            ['A' => 3, 'B' => 2, 'C' => 0],
        ];

        yield 'Limit testing Exemple 3' => [
            4,
            ['A' => 60, 'B' => 30, 'C' => 14],
            ['A' => 3, 'B' => 1, 'C' => 0],
        ];
    }
}
