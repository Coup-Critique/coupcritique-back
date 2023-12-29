<?php

namespace App\DBAL;

use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\Lexer;

/**
 * "REGEXP_REPLACE" "(" col "," regexp "," str_replacer ")"
 * @deprecated
 */
class RegexpReplace extends FunctionNode
{
    public $col;
    public $regexp;
    public $str_replacer;

    public function getSql(\Doctrine\ORM\Query\SqlWalker $sqlWalker): string
    {
        return 'REGEXP_REPLACE('
            . $this->col->dispatch($sqlWalker) . ','
            . $this->regexp->dispatch($sqlWalker) . ','
            . $this->str_replacer->dispatch($sqlWalker) . ')';
    }

    public function parse(\Doctrine\ORM\Query\Parser $parser): void
    {
        $parser->match(Lexer::T_IDENTIFIER);
        $parser->match(Lexer::T_OPEN_PARENTHESIS);
        $this->col = $parser->StringPrimary();
        $parser->match(Lexer::T_COMMA);
        $this->regexp = $parser->StringExpression();
        $parser->match(Lexer::T_COMMA);
        $this->str_replacer = $parser->StringPrimary();
        $parser->match(Lexer::T_CLOSE_PARENTHESIS);
    }
}
