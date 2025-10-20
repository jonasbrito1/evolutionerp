<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('edicts', function (Blueprint $table) {
            // Informações detalhadas do edital
            $table->string('uasg_number')->nullable()->after('edict_number'); // Número da UASG
            $table->string('process_number')->nullable()->after('uasg_number'); // Número do processo
            $table->string('modality')->nullable()->after('category'); // Pregão, Dispensa, Concorrência, etc
            $table->string('bidding_portal_url')->nullable()->after('source_url'); // Link para portal de licitação

            // Valores detalhados
            $table->decimal('labor_cost', 15, 2)->nullable()->after('minimum_value'); // Custo de mão de obra
            $table->decimal('material_cost', 15, 2)->nullable(); // Custo de materiais
            $table->decimal('tax_cost', 15, 2)->nullable(); // Custo de impostos
            $table->decimal('total_investment', 15, 2)->nullable(); // Investimento total calculado
            $table->decimal('profit_margin', 8, 2)->nullable(); // Margem de lucro %
            $table->decimal('unit_value', 15, 2)->nullable(); // Valor unitário
            $table->decimal('bid_value', 15, 2)->nullable(); // Valor de lance

            // Datas importantes
            $table->dateTime('proposal_deadline')->nullable()->after('closing_date'); // Prazo final para proposta
            $table->dateTime('session_date')->nullable(); // Data da sessão pública

            // Análise inteligente
            $table->text('object_description')->nullable(); // Objeto detalhado da licitação
            $table->json('requirements')->nullable(); // Requisitos para participar
            $table->json('company_compliance')->nullable(); // Atende aos requisitos?
            $table->json('missing_requirements')->nullable(); // Requisitos que faltam
            $table->json('available_documents')->nullable(); // Documentos disponíveis da empresa
            $table->boolean('worth_participating')->nullable(); // Vale a pena participar?
            $table->text('participation_recommendation')->nullable(); // Recomendação de participação
            $table->integer('ai_score')->nullable(); // Score de IA (0-100)

            // Processamento
            $table->enum('processing_status', ['pending', 'processing', 'completed', 'failed'])->default('pending');
            $table->text('processing_error')->nullable();
            $table->timestamp('processed_at')->nullable();

            $table->index('uasg_number');
            $table->index('process_number');
            $table->index(['processing_status', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('edicts', function (Blueprint $table) {
            $table->dropColumn([
                'uasg_number',
                'process_number',
                'modality',
                'bidding_portal_url',
                'labor_cost',
                'material_cost',
                'tax_cost',
                'total_investment',
                'profit_margin',
                'unit_value',
                'bid_value',
                'proposal_deadline',
                'session_date',
                'object_description',
                'requirements',
                'company_compliance',
                'missing_requirements',
                'available_documents',
                'worth_participating',
                'participation_recommendation',
                'ai_score',
                'processing_status',
                'processing_error',
                'processed_at',
            ]);
        });
    }
};
