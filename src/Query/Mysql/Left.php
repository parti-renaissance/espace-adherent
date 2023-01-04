<?php

namespace App\Query\Mysql;

use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\Lexer;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\SqlWalker;

class Left extends FunctionNode
{
    private $firstExpression = null;
    private $secondExpression = null;

    public function parse(Parser $parser)
    {
        $parser->match(Lexer::T_IDENTIFIER);
        $parser->match(Lexer::T_OPEN_PARENTHESIS);
        $this->firstExpression = $parser->ArithmeticPrimary();

        $parser->match(Lexer::T_COMMA);
        $this->secondExpression = $parser->SimpleArithmeticExpression();

        $parser->match(Lexer::T_CLOSE_PARENTHESIS);
    }

    public function getSql(SqlWalker $sqlWalker)
    {
        return 'LEFT('
            .$this->firstExpression->dispatch($sqlWalker)
            .', '
            .$this->secondExpression->dispatch($sqlWalker)
            .')';
    }
}
