<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->foreignId('edict_id')->constrained()->onDelete('cascade');
            $table->foreignId('responsible_user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->enum('status', ['analysis', 'documentation', 'proposal', 'participation', 'result', 'won', 'lost', 'cancelled'])->default('analysis');
            $table->date('start_date');
            $table->date('expected_end_date')->nullable();
            $table->date('actual_end_date')->nullable();
            $table->decimal('estimated_margin', 8, 2)->nullable(); // Margem estimada %
            $table->decimal('estimated_profit', 15, 2)->nullable(); // Lucro estimado
            $table->decimal('total_cost', 15, 2)->nullable(); // Custo total
            $table->text('notes')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index('company_id');
            $table->index('edict_id');
            $table->index('status');
            $table->index('responsible_user_id');
            $table->index('start_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenders');
    }
};
