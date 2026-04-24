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
        Schema::create('transcriptions', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('interview_id')->unique();

            $table->longText('transcript_text')->nullable();

            $table->float('whisper_confidence')->nullable();

            $table->string('language')->nullable();

            $table->integer('progress_pct')->default(0);

            $table->foreign('interview_id')
                ->references('id')
                ->on('interviews');

            $table->index('progress_pct');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transcriptions');
    }
};
