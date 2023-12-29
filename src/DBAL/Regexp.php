<?php

namespace App\DBAL;

use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\Lexer;

/**
 * Usage : REGEXP(col,regexp) = 1
 * @deprecated
 */
class Regexp extends FunctionNode
{
    public $col;
    public $regexp;

    public function getSql(\Doctrine\ORM\Query\SqlWalker $sqlWalker): string
    {
        return $this->col->dispatch($sqlWalker) . ' REGEXP ' . $this->regexp->dispatch($sqlWalker);
    }

    public function parse(\Doctrine\ORM\Query\Parser $parser): void
    {
        $parser->match(Lexer::T_IDENTIFIER);
        $parser->match(Lexer::T_OPEN_PARENTHESIS);
        $this->col = $parser->StringPrimary();
        $parser->match(Lexer::T_COMMA);
        $this->regexp = $parser->StringExpression();
        $parser->match(Lexer::T_CLOSE_PARENTHESIS);
    }
}
