<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('financial_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['receita', 'despesa']); // Receita ou Despesa
            $table->string('category'); // Categoria (ex: Licitação, Fornecedor, Salário, etc)
            $table->string('description');
            $table->decimal('amount', 15, 2); // Valor
            $table->date('due_date'); // Data de vencimento
            $table->date('payment_date')->nullable(); // Data de pagamento/recebimento
            $table->enum('status', ['pendente', 'pago', 'recebido', 'atrasado', 'cancelado'])->default('pendente');
            $table->string('payment_method')->nullable(); // Método de pagamento (Dinheiro, Transferência, Boleto, etc)
            $table->string('document_number')->nullable(); // Número do documento (Nota fiscal, recibo, etc)
            $table->foreignId('related_edict_id')->nullable()->constrained('edicts')->onDelete('set null'); // Relacionado a licitação
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable(); // Dados adicionais
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id', 'type']);
            $table->index(['status', 'due_date']);
            $table->index('payment_date');
        });

        // Criar tabela de categorias financeiras
        Schema::create('financial_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('type', ['receita', 'despesa', 'ambos']);
            $table->string('icon')->nullable();
            $table->string('color')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        // Inserir categorias padrão
        DB::table('financial_categories')->insert([
            ['name' => 'Licitação Ganha', 'type' => 'receita', 'icon' => '🏆', 'color' => 'green', 'active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Serviços Prestados', 'type' => 'receita', 'icon' => '💼', 'color' => 'blue', 'active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Outras Receitas', 'type' => 'receita', 'icon' => '💰', 'color' => 'teal', 'active' => true, 'created_at' => now(), 'updated_at' => now()],

            ['name' => 'Salários', 'type' => 'despesa', 'icon' => '👥', 'color' => 'orange', 'active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Fornecedores', 'type' => 'despesa', 'icon' => '🏭', 'color' => 'red', 'active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Impostos e Taxas', 'type' => 'despesa', 'icon' => '📊', 'color' => 'purple', 'active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Aluguel', 'type' => 'despesa', 'icon' => '🏢', 'color' => 'indigo', 'active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Contas de Consumo', 'type' => 'despesa', 'icon' => '⚡', 'color' => 'yellow', 'active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Material de Escritório', 'type' => 'despesa', 'icon' => '📝', 'color' => 'gray', 'active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Manutenção', 'type' => 'despesa', 'icon' => '🔧', 'color' => 'pink', 'active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Outras Despesas', 'type' => 'despesa', 'icon' => '💸', 'color' => 'red', 'active' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('financial_transactions');
        Schema::dropIfExists('financial_categories');
    }
};
