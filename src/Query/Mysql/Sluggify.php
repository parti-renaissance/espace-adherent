<?php

namespace App\Query\Mysql;

use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\Lexer;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\SqlWalker;

class Sluggify extends FunctionNode
{
    public const REGEXP_PATTERN = '[^a-zA-Z0-9]+';

    private $firstExpression;

    public function parse(Parser $parser)
    {
        $parser->match(Lexer::T_IDENTIFIER);
        $parser->match(Lexer::T_OPEN_PARENTHESIS);
        $this->firstExpression = $parser->ArithmeticPrimary();
        $parser->match(Lexer::T_CLOSE_PARENTHESIS);
    }

    public function getSql(SqlWalker $sqlWalker)
    {
        return sprintf(
            "REGEXP_REPLACE(%s, '%s', '')",
            $this->firstExpression->dispatch($sqlWalker),
            self::REGEXP_PATTERN
        );
    }
}
