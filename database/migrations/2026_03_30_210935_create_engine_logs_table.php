<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('engine_logs', function (Blueprint $table): void {
            $table->id();
            $table->string('action_name');
            $table->string('event')->nullable();
            $table->string('status')->default('executed');
            $table->nullableMorphs('actor');
            $table->morphs('subject');
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->json('payload')->nullable();
            $table->timestamps();

            $table->index(['action_name', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('engine_logs');
    }
};
