<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('edicts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->string('edict_number')->unique();
            $table->string('organ'); // Órgão licitante
            $table->string('category')->nullable(); // Categoria (TI, Limpeza, etc)
            $table->text('description'); // Descrição do objeto
            $table->text('requirements_text')->nullable(); // Texto extraído dos requisitos
            $table->decimal('estimated_value', 15, 2)->nullable();
            $table->decimal('minimum_value', 15, 2)->nullable();
            $table->date('publication_date')->nullable();
            $table->date('closing_date')->nullable(); // Data de encerramento
            $table->date('opening_date')->nullable(); // Data de abertura dos lances
            $table->enum('status', ['draft', 'imported', 'analyzed', 'participated', 'closed'])->default('draft');
            $table->string('source_url')->nullable(); // URL da origem
            $table->string('file_path')->nullable(); // Caminho do arquivo PDF/DOC
            $table->json('extracted_data')->nullable(); // Dados extraídos estruturados
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('company_id');
            $table->index('edict_number');
            $table->index('organ');
            $table->index('status');
            $table->index('closing_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('edicts');
    }
};
