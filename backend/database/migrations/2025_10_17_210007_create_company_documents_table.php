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
        Schema::create('company_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->string('document_type'); // CNPJ, Contrato Social, Certidões, Balanço, etc
            $table->string('document_name');
            $table->string('file_path');
            $table->string('file_size')->nullable();
            $table->string('mime_type')->nullable();
            $table->date('issue_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->enum('status', ['valid', 'expired', 'pending_renewal', 'invalid'])->default('valid');
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable(); // Dados extraídos do documento
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id', 'document_type']);
            $table->index('expiry_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_documents');
    }
};
