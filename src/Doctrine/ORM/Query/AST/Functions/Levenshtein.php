<?php

namespace App\Doctrine\ORM\Query\AST\Functions;

use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\Lexer;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\SqlWalker;

// Took from https://github.com/fza/mysql-doctrine-levenshtein-function#define-mysql-functions, thanks to him !
class Levenshtein extends FunctionNode
{
    public ?object $firstStringExpression = null;
    public ?object $secondStringExpression = null;

    public function getSql(SqlWalker $sqlWalker): string
    {
        return sprintf(
            'levenshtein(%s, %s)',
            $this->firstStringExpression->dispatch($sqlWalker),
            $this->secondStringExpression->dispatch($sqlWalker)
        );
    }

    public function parse(Parser $parser): void
    {
        // levenshtein(str1, str2)
        $parser->match(Lexer::T_IDENTIFIER);
        $parser->match(Lexer::T_OPEN_PARENTHESIS);
        $this->firstStringExpression = $parser->ArithmeticPrimary();
        $parser->match(Lexer::T_COMMA);
        $this->secondStringExpression = $parser->ArithmeticPrimary();
        $parser->match(Lexer::T_CLOSE_PARENTHESIS);
    }
}
