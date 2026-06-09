<?php declare(strict_types=1);

namespace Frosh\Mjml\Twig\Node;

use Frosh\Mjml\Twig\MjmlComponentExtension;
use Twig\Attribute\YieldReady;
use Twig\Compiler;
use Twig\Node\CaptureNode;
use Twig\Node\Expression\AbstractExpression;
use Twig\Node\Node;

#[YieldReady]
class MjmlComponentNode extends Node
{
    public function __construct(AbstractExpression $name, ?AbstractExpression $variables, Node $body, int $lineno)
    {
        $nodes = ['name' => $name, 'body' => new CaptureNode($body, $lineno)];
        if ($variables !== null) {
            $nodes['variables'] = $variables;
        }

        parent::__construct($nodes, [], $lineno);
    }

    public function compile(Compiler $compiler): void
    {
        $compiler
            ->addDebugInfo($this)
            ->write('$mjmlBody = ')
            ->subcompile($this->getNode('body'))
            ->raw(";\n")
            ->write('$mjmlVars = ');

        if ($this->hasNode('variables')) {
            $compiler->subcompile($this->getNode('variables'));
        } else {
            $compiler->raw('[]');
        }

        $compiler
            ->raw(";\n")
            ->write('yield $this->env->getExtension(\\' . MjmlComponentExtension::class . '::class)->renderEmbedded($this->env, $context, ')
            ->subcompile($this->getNode('name'))
            ->raw(', (string) $mjmlBody, $mjmlVars);' . "\n");
    }
}
