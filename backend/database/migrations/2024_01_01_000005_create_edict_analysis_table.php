<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('edict_analysis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('edict_id')->unique()->constrained('edicts')->onDelete('cascade');
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->integer('compatibility_score')->nullable(); // 0-100
            $table->integer('documentation_score')->nullable(); // 0-100
            $table->integer('margin_score')->nullable(); // 0-100
            $table->integer('success_rate_score')->nullable(); // 0-100
            $table->integer('viability_score'); // 0-100 (total)
            $table->enum('recommendation', ['not_recommended', 'viable_with_caution', 'highly_recommended'])->default('viable_with_caution');
            $table->json('strengths')->nullable(); // Pontos positivos
            $table->json('weaknesses')->nullable(); // Pontos de atenção
            $table->json('missing_documents')->nullable(); // Documentos faltando
            $table->json('next_steps')->nullable(); // Próximos passos
            $table->text('justification')->nullable(); // Justificativa textual
            $table->timestamps();

            $table->index('company_id');
            $table->index('edict_id');
            $table->index('viability_score');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('edict_analysis');
    }
};
