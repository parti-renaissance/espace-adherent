<?php

namespace AppBundle\Query\Mysql;

use Doctrine\DBAL\DBALException;
use Doctrine\ORM\Query\Lexer;
use Doctrine\ORM\Query\Parser;
use Doctrine\DBAL\Platforms\MySqlPlatform;
use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\SqlWalker;

class JsonContains extends FunctionNode
{
    const FUNCTION_NAME = 'JSON_CONTAINS';

    /**
     * @var \Doctrine\ORM\Query\AST\Node
     */
    public $jsonDocExpr;

    /**
     * @var \Doctrine\ORM\Query\AST\Node
     */
    public $jsonValExpr;

    /**
     * @var \Doctrine\ORM\Query\AST\Node
     */
    public $jsonPathExpr;

    /**
     * @throws DBALException
     */
    public function getSql(SqlWalker $sqlWalker): string
    {
        $jsonDoc = $sqlWalker->walkStringPrimary($this->jsonDocExpr);
        $jsonVal = $sqlWalker->walkStringPrimary($this->jsonValExpr);
        $jsonPath = '';
        if ($this->jsonPathExpr) {
            $jsonPath = ', '.$sqlWalker->walkStringPrimary($this->jsonPathExpr);
        }

        if ($sqlWalker->getConnection()->getDatabasePlatform() instanceof MySqlPlatform) {
            return sprintf('%s(%s, %s)', static::FUNCTION_NAME, $jsonDoc, $jsonVal.$jsonPath);
        }

        throw DBALException::notSupported(static::FUNCTION_NAME);
    }

    /**
     * @throws \Doctrine\ORM\Query\QueryException
     */
    public function parse(Parser $parser): void
    {
        $parser->match(Lexer::T_IDENTIFIER);
        $parser->match(Lexer::T_OPEN_PARENTHESIS);
        $this->jsonDocExpr = $parser->StringPrimary();
        $parser->match(Lexer::T_COMMA);
        $this->jsonValExpr = $parser->StringPrimary();
        if ($parser->getLexer()->isNextToken(Lexer::T_COMMA)) {
            $parser->match(Lexer::T_COMMA);
            $this->jsonPathExpr = $parser->StringPrimary();
        }

        $parser->match(Lexer::T_CLOSE_PARENTHESIS);
    }
}
