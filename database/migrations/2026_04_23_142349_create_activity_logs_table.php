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
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('user_id');

            $table->string('entity_type');

            $table->unsignedBigInteger('entity_id');

            $table->string('action');

            $table->json('metadata')->nullable();

            // Foreign key
            $table->foreign('user_id')
                ->references('id')
                ->on('users');

            // Performance indexes (VERY important for logs)
            $table->index(['entity_type', 'entity_id']);
            $table->index(['user_id', 'action']);
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
