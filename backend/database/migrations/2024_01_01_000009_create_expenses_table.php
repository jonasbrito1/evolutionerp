<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->foreignId('tender_id')->nullable()->constrained()->onDelete('set null');
            $table->string('description');
            $table->enum('category', ['operational', 'salaries', 'infrastructure', 'marketing', 'tender_costs', 'other'])->default('other');
            $table->decimal('amount', 15, 2);
            $table->date('expense_date');
            $table->enum('status', ['budgeted', 'actual', 'pending_payment'])->default('budgeted');
            $table->string('supplier')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('company_id');
            $table->index('tender_id');
            $table->index('category');
            $table->index('expense_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
