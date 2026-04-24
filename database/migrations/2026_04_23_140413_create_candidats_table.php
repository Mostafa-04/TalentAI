<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('candidats', function (Blueprint $table) {
            $table->id();
            $table->string('full_name');
            $table->string('email')->nullable()->unique();
            $table->string('phone')->nullable();
            $table->string('current_title')->nullable();
            $table->string('current_company')->nullable();
            $table->string('location')->nullable();
            $table->integer('experience_years')->nullable();
            $table->string('education_level')->nullable();
            $table->enum('source', [
                'linkedin',
                'indeed',
                'facebook',
                'manual',
                'cv_upload',
            ]);
            $table->string('source_url')->nullable();
            $table->enum('status', [
                'sourced',
                'contacted',
                'interview',
                'recommended',
                'offer',
                'rejected',
            ]);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('candidats');
    }
};
