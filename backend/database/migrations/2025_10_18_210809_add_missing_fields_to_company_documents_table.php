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
        Schema::table('company_documents', function (Blueprint $table) {
            // Add missing fields if they don't exist
            if (!Schema::hasColumn('company_documents', 'file_name')) {
                $table->string('file_name')->nullable()->after('file_path');
            }
            if (!Schema::hasColumn('company_documents', 'description')) {
                $table->text('description')->nullable()->after('notes');
            }
            if (!Schema::hasColumn('company_documents', 'reference_number')) {
                $table->string('reference_number')->nullable()->after('document_name');
            }
            if (!Schema::hasColumn('company_documents', 'document_category_id')) {
                $table->foreignId('document_category_id')->nullable()->after('company_id');
            }

            // Update status enum to include 'expiring_soon'
            if (Schema::hasColumn('company_documents', 'status')) {
                $table->enum('status', ['valid', 'expired', 'expiring_soon', 'pending_review', 'invalid'])->default('valid')->change();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('company_documents', function (Blueprint $table) {
            if (Schema::hasColumn('company_documents', 'file_name')) {
                $table->dropColumn('file_name');
            }
            if (Schema::hasColumn('company_documents', 'description')) {
                $table->dropColumn('description');
            }
            if (Schema::hasColumn('company_documents', 'reference_number')) {
                $table->dropColumn('reference_number');
            }
            if (Schema::hasColumn('company_documents', 'document_category_id')) {
                $table->dropForeign(['document_category_id']);
                $table->dropColumn('document_category_id');
            }
        });
    }
};
