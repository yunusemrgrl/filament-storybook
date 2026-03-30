<?php

declare(strict_types=1);

namespace App\Support\Engine\Compiler\Contracts;

use App\Support\Engine\Ast\EngineNode;
use App\Support\Engine\Compiler\CompileContext;
use App\Support\Engine\Compiler\CompiledNode;

interface CompilesEngineNode
{
    public function supports(EngineNode $node): bool;

    /**
     * @param  array<int, CompiledNode>  $compiledChildren
     */
    public function compile(EngineNode $node, array $compiledChildren, CompileContext $context): CompiledNode;
}
