<?php

declare(strict_types=1);

namespace App\StarterKits\StrukturaEngine;

use App\StarterKits\StrukturaEngine\Actions\ActionExecutor;
use App\StarterKits\StrukturaEngine\Actions\ActionRegistry;
use App\StarterKits\StrukturaEngine\Actions\EffectDispatcher;
use App\StarterKits\StrukturaEngine\Compilers\NavigationCompiler;
use App\StarterKits\StrukturaEngine\Compilers\PageCompiler;
use App\StarterKits\StrukturaEngine\Contracts\CompilesNavigationAst;
use App\StarterKits\StrukturaEngine\Contracts\CompilesPageAst;
use App\StarterKits\StrukturaEngine\Http\PreviewTokenResolver;
use App\StarterKits\StrukturaEngine\Panel\StrukturaPanelBridge;
use App\StarterKits\StrukturaEngine\Services\EngineCompilerRuntime;
use App\StarterKits\StrukturaEngine\Services\ModelIntrospector;
use App\StarterKits\StrukturaEngine\Services\ModelRegistry;
use App\StarterKits\StrukturaEngine\Services\NodeRuleMatrix;
use App\StarterKits\StrukturaEngine\Workflow\QueryScopeApplier;
use App\StarterKits\StrukturaEngine\Workflow\StrukturaStateMachine;
use Illuminate\Support\ServiceProvider;

class StrukturaEngineServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ModelRegistry::class);
        $this->app->singleton(ModelIntrospector::class);
        $this->app->singleton(NodeRuleMatrix::class);
        $this->app->singleton(EngineCompilerRuntime::class);
        $this->app->singleton(NavigationCompiler::class);
        $this->app->singleton(PageCompiler::class);
        $this->app->singleton(StrukturaPanelBridge::class);
        $this->app->singleton(PreviewTokenResolver::class);
        $this->app->singleton(ActionRegistry::class);
        $this->app->singleton(StrukturaStateMachine::class);
        $this->app->singleton(EffectDispatcher::class);
        $this->app->singleton(QueryScopeApplier::class);
        $this->app->singleton(ActionExecutor::class);

        $this->app->alias(NavigationCompiler::class, CompilesNavigationAst::class);
        $this->app->alias(PageCompiler::class, CompilesPageAst::class);

        $this->app->alias(ModelRegistry::class, \App\Support\Engine\ModelRegistry::class);
        $this->app->alias(ModelIntrospector::class, \App\Support\Engine\ModelIntrospector::class);
        $this->app->alias(NodeRuleMatrix::class, \App\Support\Engine\NodeRuleMatrix::class);
        $this->app->alias(EngineCompilerRuntime::class, \App\Support\Engine\Compiler\EngineCompilerRuntime::class);
    }
}
