<?php

namespace App\DBAL;


use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\Lexer;

/**
 * "DATE_FORMAT" "(" col "," formatter ")"
 */
class DateFormat extends FunctionNode
{
    public $col;
    public $formatter;

    public function getSql(\Doctrine\ORM\Query\SqlWalker $sqlWalker): string
    {
        return "DATE_FORMAT("
            . $this->col->dispatch($sqlWalker) . ','
            . $this->formatter->dispatch($sqlWalker) . ")";
    }

    public function parse(\Doctrine\ORM\Query\Parser $parser): void
    {
        $parser->match(Lexer::T_IDENTIFIER);
        $parser->match(Lexer::T_OPEN_PARENTHESIS);
        $this->col = $parser->StringPrimary();
        $parser->match(Lexer::T_COMMA);
        $this->formatter = $parser->StringPrimary();
        $parser->match(Lexer::T_CLOSE_PARENTHESIS);
    }
}
