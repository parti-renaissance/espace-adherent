<?php

declare(strict_types=1);

namespace App\Doctrine\DBAL;

use Doctrine\DBAL\Platforms\Exception\NotSupported;
use Doctrine\DBAL\Platforms\MySQLPlatform;
use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\AST\Node;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\QueryException;
use Doctrine\ORM\Query\SqlWalker;
use Doctrine\ORM\Query\TokenType;

class JsonContains extends FunctionNode
{
    public const FUNCTION_NAME = 'JSON_CONTAINS';

    /**
     * @var Node
     */
    public $jsonDocExpr;

    /**
     * @var Node
     */
    public $jsonValExpr;

    /**
     * @var Node
     */
    public $jsonPathExpr;

    /**
     * @throws NotSupported
     */
    public function getSql(SqlWalker $sqlWalker): string
    {
        $jsonDoc = $sqlWalker->walkStringPrimary($this->jsonDocExpr);
        $jsonVal = $sqlWalker->walkStringPrimary($this->jsonValExpr);
        $jsonPath = '';
        if ($this->jsonPathExpr) {
            $jsonPath = ', '.$sqlWalker->walkStringPrimary($this->jsonPathExpr);
        }

        if ($sqlWalker->getConnection()->getDatabasePlatform() instanceof MySQLPlatform) {
            return \sprintf('%s(%s, %s)', static::FUNCTION_NAME, $jsonDoc, $jsonVal.$jsonPath);
        }

        throw NotSupported::new(static::FUNCTION_NAME);
    }

    /**
     * @throws QueryException
     */
    public function parse(Parser $parser): void
    {
        $parser->match(TokenType::T_IDENTIFIER);
        $parser->match(TokenType::T_OPEN_PARENTHESIS);
        $this->jsonDocExpr = $parser->StringPrimary();
        $parser->match(TokenType::T_COMMA);
        $this->jsonValExpr = $parser->StringPrimary();
        if ($parser->getLexer()->isNextToken(TokenType::T_COMMA)) {
            $parser->match(TokenType::T_COMMA);
            $this->jsonPathExpr = $parser->StringPrimary();
        }

        $parser->match(TokenType::T_CLOSE_PARENTHESIS);
    }
}
