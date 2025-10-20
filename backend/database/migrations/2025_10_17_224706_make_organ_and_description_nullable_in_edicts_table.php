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
            $table->string('organ')->nullable()->change();
            $table->text('description')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('edicts', function (Blueprint $table) {
            $table->string('organ')->nullable(false)->change();
            $table->text('description')->nullable(false)->change();
        });
    }
};
