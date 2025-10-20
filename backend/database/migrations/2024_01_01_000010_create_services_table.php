<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('category'); // Consultoria, Limpeza, TI, etc
            $table->text('description')->nullable();
            $table->decimal('base_price', 15, 2)->nullable();
            $table->string('unit')->nullable(); // hora, dia, projeto, etc
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('company_id');
            $table->index('category');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
