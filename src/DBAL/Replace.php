<?php

namespace App\DBAL;

use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\Lexer;

/**
 * "REPLACE" "(" col "," str_to_replace "," str_replacer ")"
 */
class Replace extends FunctionNode
{
    public $col;
    public $str_to_replace;
    public $str_replacer;

    public function getSql(\Doctrine\ORM\Query\SqlWalker $sqlWalker): string
    {
        return 'REPLACE('
            . $this->col->dispatch($sqlWalker) . ','
            . $this->str_to_replace->dispatch($sqlWalker) . ','
            . $this->str_replacer->dispatch($sqlWalker) . ')';
    }

    public function parse(\Doctrine\ORM\Query\Parser $parser): void
    {
        $parser->match(Lexer::T_IDENTIFIER);
        $parser->match(Lexer::T_OPEN_PARENTHESIS);
        $this->col = $parser->StringPrimary();
        $parser->match(Lexer::T_COMMA);
        $this->str_to_replace = $parser->StringExpression();
        $parser->match(Lexer::T_COMMA);
        $this->str_replacer = $parser->StringExpression();
        $parser->match(Lexer::T_CLOSE_PARENTHESIS);
    }
}
