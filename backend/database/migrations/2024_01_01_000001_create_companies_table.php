<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('cnpj')->unique();
            $table->string('industry')->nullable();
            $table->date('founding_date')->nullable();
            $table->text('address')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('logo')->nullable();
            $table->text('description')->nullable();
            $table->string('status')->default('active'); // active, inactive
            $table->timestamps();

            $table->index('cnpj');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
