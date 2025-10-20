<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('revenues', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->foreignId('tender_id')->nullable()->constrained()->onDelete('set null');
            $table->string('description');
            $table->decimal('amount', 15, 2);
            $table->date('billing_date');
            $table->date('payment_date')->nullable();
            $table->enum('status', ['pending', 'received', 'partially_received', 'delayed'])->default('pending');
            $table->string('payment_method')->nullable(); // Transferência, Cheque, etc
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('company_id');
            $table->index('tender_id');
            $table->index('status');
            $table->index('billing_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('revenues');
    }
};
