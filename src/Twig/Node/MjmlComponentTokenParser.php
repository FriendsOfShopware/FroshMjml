<?php declare(strict_types=1);

namespace Frosh\Mjml\Twig\Node;

use Twig\Node\Node;
use Twig\Token;
use Twig\TokenParser\AbstractTokenParser;

class MjmlComponentTokenParser extends AbstractTokenParser
{
    public function parse(Token $token): Node
    {
        $lineno = $token->getLine();
        $stream = $this->parser->getStream();

        $name = $this->parser->parseExpression();

        $variables = null;
        if ($stream->nextIf(Token::NAME_TYPE, 'with')) {
            $variables = $this->parser->parseExpression();
        }

        $stream->expect(Token::BLOCK_END_TYPE);
        $body = $this->parser->subparse([$this, 'decideBlockEnd'], true);
        $stream->expect(Token::BLOCK_END_TYPE);

        return new MjmlComponentNode($name, $variables, $body, $lineno);
    }

    public function decideBlockEnd(Token $token): bool
    {
        return $token->test('endmjml');
    }

    public function getTag(): string
    {
        return 'mjml';
    }
}
