<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->string('company_name');
            $table->string('cnpj')->nullable();
            $table->string('contact_person')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->string('specialty')->nullable(); // Categoria de fornecimento
            $table->decimal('rating', 3, 2)->nullable(); // 1-5 stars
            $table->enum('status', ['active', 'inactive', 'blocked'])->default('active');
            $table->integer('total_transactions')->default(0);
            $table->decimal('total_spent', 15, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('company_id');
            $table->index('specialty');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('suppliers');
    }
};
