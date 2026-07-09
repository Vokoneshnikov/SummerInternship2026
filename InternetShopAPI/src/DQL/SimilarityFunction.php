<?php

namespace App\DQL;

use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\SqlWalker;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\TokenType;

class SimilarityFunction extends FunctionNode {

    private $firstExpression;
    private $secondExpression;

    public function parse(Parser $parser): void
    {
        $parser->match(TokenType::T_IDENTIFIER);

        $parser->match(TokenType::T_OPEN_PARENTHESIS);

        $this->firstExpression = $parser->StringPrimary();

        $parser->match(TokenType::T_COMMA);

        $this->secondExpression = $parser->StringPrimary();

        $parser->match(TokenType::T_CLOSE_PARENTHESIS);
    }
    public function getSql(sqlWalker $sqlWalker): string
    {
        return sprintf(
            'SIMILARITY(%s, %s)',
            $this->firstExpression->dispatch($sqlWalker),
            $this->secondExpression->dispatch($sqlWalker)
        );
    }
}
