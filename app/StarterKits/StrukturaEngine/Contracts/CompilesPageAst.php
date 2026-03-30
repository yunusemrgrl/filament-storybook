<?php

declare(strict_types=1);

namespace App\StarterKits\StrukturaEngine\Contracts;

use App\Models\Page;
use App\Support\Engine\Ast\EngineNodeCollection;
use App\Support\Engine\Compiler\CompiledNode;
use Filament\Forms\Components\Field;
use Filament\Schemas\Components\Component as SchemaComponent;
use Filament\Widgets\WidgetConfiguration;

interface CompilesPageAst
{
    /**
     * @param  array<int, array<string, mixed>>|EngineNodeCollection|null  $nodes
     * @return array<int, CompiledNode>
     */
    public function compileNodes(Page $page, array|EngineNodeCollection|null $nodes = null, string $mode = 'runtime'): array;

    /**
     * @param  array<int, array<string, mixed>>|EngineNodeCollection|null  $nodes
     * @return array<int, SchemaComponent|Field>
     */
    public function compileContentComponents(Page $page, array|EngineNodeCollection|null $nodes = null, string $mode = 'runtime'): array;

    /**
     * @return array<int, WidgetConfiguration>
     */
    public function compileWidgetConfigurations(Page $page, string $mode = 'runtime'): array;
}
