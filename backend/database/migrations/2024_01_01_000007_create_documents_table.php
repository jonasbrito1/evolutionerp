<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->foreignId('tender_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('filename');
            $table->string('file_path');
            $table->string('file_size'); // Tamanho em bytes
            $table->string('mime_type')->nullable();
            $table->enum('category', ['company', 'certification', 'legal', 'proposal', 'tender_docs', 'compliance', 'communication'])->default('company');
            $table->integer('version')->default(1);
            $table->foreignId('uploaded_by')->constrained('users')->onDelete('cascade');
            $table->text('description')->nullable();
            $table->json('tags')->nullable();
            $table->boolean('is_public')->default(false);
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->index('company_id');
            $table->index('tender_id');
            $table->index('category');
            $table->index('uploaded_by');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
