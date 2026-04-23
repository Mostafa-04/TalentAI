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
        Schema::create('integrations', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();

            $table->unsignedBigInteger('user_id');

            $table->enum('provider', [
                'linkedin',
                'indeed',
                'facebook',
                'whisper',
                'claude',
                'google_calendar'
            ]);

            $table->text('api_token');

            $table->integer('credits_used')->default(0);

            $table->integer('credits_limit')->nullable();

            $table->timestamp('token_expires_at')->nullable();

            $table->boolean('active')->default(true);

            // Foreign key
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ;

            // Indexes (important for SaaS + billing + monitoring)
            $table->index(['user_id', 'provider']);
            $table->index('active');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('integrations');
    }
};
