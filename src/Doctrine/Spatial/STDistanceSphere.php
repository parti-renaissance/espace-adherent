<?php

namespace App\Doctrine\Spatial;

use CrEOF\Spatial\ORM\Query\AST\Functions\PostgreSql\STDistanceSphere as BaseSTDistanceSphere;

class STDistanceSphere extends BaseSTDistanceSphere
{
    protected $functionName = 'ST_DistanceSphere';
}
