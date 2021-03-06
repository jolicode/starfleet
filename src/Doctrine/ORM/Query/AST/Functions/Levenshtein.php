<?php

/*
 * This file is part of the Starfleet Project.
 *
 * (c) Starfleet <msantostefano@jolicode.com>
 *
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 */

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

    public function getSql(SqlWalker $sqlWalker)
    {
        return sprintf(
            'levenshtein(%s, %s)',
            $this->firstStringExpression->dispatch($sqlWalker),
            $this->secondStringExpression->dispatch($sqlWalker)
        );
    }

    public function parse(Parser $parser)
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
