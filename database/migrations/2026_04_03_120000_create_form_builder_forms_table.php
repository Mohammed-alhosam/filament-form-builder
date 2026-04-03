<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('form_builder_forms', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->json('name');
            $table->string('slug')->unique();
            $table->json('description')->nullable();
            $table->string('status')->default('draft')->index();
            $table->boolean('is_active')->default(true)->index();
            $table->json('success_message')->nullable();
            $table->json('submit_label')->nullable();
            $table->json('settings')->nullable();
            $table->json('notifications')->nullable();
            $table->json('availability')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('form_builder_forms');
    }
};
