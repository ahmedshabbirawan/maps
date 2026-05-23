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
            $table->enum('type', ['string', 'number', 'boolean'])->default('string');
            $table->timestamps();

            $table->index(['collection_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attributes');
    }
};
