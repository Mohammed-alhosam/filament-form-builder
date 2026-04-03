<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('form_builder_fields', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('form_id')->constrained('form_builder_forms')->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('form_builder_fields')->nullOnDelete();
            $table->string('type')->index();
            $table->string('key');
            $table->json('label');
            $table->json('placeholder')->nullable();
            $table->json('help_text')->nullable();
            $table->json('default_value')->nullable();
            $table->boolean('is_required')->default(false)->index();
            $table->string('width')->default('full');
            $table->unsignedInteger('sort_order')->default(0)->index();
            $table->json('validation_rules')->nullable();
            $table->json('options')->nullable();
            $table->json('settings')->nullable();
            $table->timestamps();

            $table->unique(['form_id', 'key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('form_builder_fields');
    }
};
