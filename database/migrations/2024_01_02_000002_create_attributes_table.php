<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attributes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('collection_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->enum('type', ['string', 'number', 'date', 'boolean'])->default('string');
            $table->boolean('is_visible')->default(true);
            $table->timestamps();

            $table->unique(['collection_id', 'slug']);
            $table->index(['collection_id', 'is_visible']);
            $table->index(['collection_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attributes');
    }
};
