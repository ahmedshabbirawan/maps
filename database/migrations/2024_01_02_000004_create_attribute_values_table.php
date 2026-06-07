<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attribute_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attribute_id')->constrained('attributes')->cascadeOnDelete();
            $table->string('entity_type');
            $table->unsignedBigInteger('entity_id');
            $table->longText('value')->nullable();
            $table->timestamps();

            $table->unique(['attribute_id', 'entity_type', 'entity_id']);
            $table->index(['entity_type', 'entity_id']);
          //  $table->index(['attribute_id', 'value']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attribute_values');
    }
};
