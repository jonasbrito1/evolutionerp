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
        Schema::create('chat_messages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('session_id')->index();
            $table->enum('role', ['user', 'assistant'])->default('user');
            $table->text('message');
            $table->text('response')->nullable();
            $table->json('context')->nullable();
            $table->json('metadata')->nullable();
            $table->string('intent')->nullable();
            $table->boolean('has_attachments')->default(false);
            $table->timestamps();

            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');

            $table->index(['company_id', 'session_id']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chat_messages');
    }
};
