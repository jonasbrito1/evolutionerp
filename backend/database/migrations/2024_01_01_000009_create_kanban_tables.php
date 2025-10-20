<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tabela de colunas do Kanban
        Schema::create('kanban_columns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->string('name'); // Ex: "Nova Oportunidade", "Orçamento", "Aprovado", etc
            $table->string('color')->default('#3B82F6'); // Cor da coluna
            $table->integer('order')->default(0); // Ordem de exibição
            $table->boolean('is_final')->default(false); // Se é coluna final (disputa concluída)
            $table->timestamps();

            $table->index(['company_id', 'order']);
        });

        // Tabela de cards do Kanban (vinculados a editais)
        Schema::create('kanban_cards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->foreignId('kanban_column_id')->constrained()->onDelete('cascade');
            $table->foreignId('edict_id')->nullable()->constrained()->onDelete('cascade');

            // Informações básicas do card
            $table->string('title'); // Título do pregão
            $table->text('description')->nullable(); // Descrição curta
            $table->string('edict_number')->nullable(); // Número do edital
            $table->decimal('estimated_value', 15, 2)->nullable();
            $table->date('deadline')->nullable(); // Prazo importante
            $table->date('session_date')->nullable(); // Data da disputa

            // Checklist de validações
            $table->boolean('has_budget')->default(false); // Orçamento feito?
            $table->boolean('has_suppliers')->default(false); // Fornecedores confirmados?
            $table->boolean('has_certificates')->default(false); // Atestados OK?
            $table->boolean('has_documents')->default(false); // Documentação pronta?
            $table->boolean('team_approved')->default(false); // Equipe aprovou?

            // Controle
            $table->integer('order')->default(0); // Ordem dentro da coluna
            $table->foreignId('assigned_to')->nullable()->constrained('users')->onDelete('set null'); // Responsável
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade'); // Quem criou
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
            $table->text('notes')->nullable(); // Observações

            $table->timestamps();

            $table->index(['company_id', 'kanban_column_id', 'order']);
            $table->index('edict_id');
            $table->index('deadline');
        });

        // Tabela de comentários nos cards
        Schema::create('kanban_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kanban_card_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->text('comment');
            $table->timestamps();

            $table->index('kanban_card_id');
        });

        // Tabela de histórico de movimentações
        Schema::create('kanban_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kanban_card_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('from_column_id')->nullable()->constrained('kanban_columns')->onDelete('set null');
            $table->foreignId('to_column_id')->constrained('kanban_columns')->onDelete('cascade');
            $table->text('note')->nullable();
            $table->timestamps();

            $table->index('kanban_card_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kanban_history');
        Schema::dropIfExists('kanban_comments');
        Schema::dropIfExists('kanban_cards');
        Schema::dropIfExists('kanban_columns');
    }
};
