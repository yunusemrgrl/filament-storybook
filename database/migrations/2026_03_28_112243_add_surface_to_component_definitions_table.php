<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('component_definitions', function (Blueprint $table): void {
            $table->string('surface')
                ->default('page')
                ->after('handle')
                ->index();
        });
    }

    public function down(): void
    {
        Schema::table('component_definitions', function (Blueprint $table): void {
            $table->dropIndex(['surface']);
            $table->dropColumn('surface');
        });
    }
};
