<?php

namespace App\Validator\TerritorialCouncil;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 * @Target({"CLASS", "ANNOTATION"})
 */
class UniqueTerritorialCouncilMember extends Constraint
{
    public const QUALITY_REFERENT = 'referent';
    public const QUALITY_LRE = 'lre_manager';
    public const QUALITY_REFERENT_JAM = 'referent_jam';

    public const QUALITIES = [
        self::QUALITY_REFERENT,
        self::QUALITY_LRE,
        self::QUALITY_REFERENT_JAM,
    ];

    public const QUALITIES_LABELS = [
        self::QUALITY_REFERENT => 'territorial_council.referent',
        self::QUALITY_LRE => 'territorial_council.lre_manager',
        self::QUALITY_REFERENT_JAM => 'territorial_council.referent_jam',
    ];

    public $qualities = [];
    public $message = 'territorial_council.member.unique';

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }

    public function getRequiredOptions()
    {
        return ['qualities'];
    }
}
