<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('form_builder_bindings', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('form_id')->constrained('form_builder_forms')->cascadeOnDelete();
            $table->string('bindable_type');
            $table->unsignedBigInteger('bindable_id');
            $table->string('context')->nullable()->index();
            $table->json('settings')->nullable();
            $table->timestamps();

            $table->index(['bindable_type', 'bindable_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('form_builder_bindings');
    }
};
