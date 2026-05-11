<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('alumni_profiles', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            $table->integer('graduation_year')->nullable();
            $table->string('degree_level')->nullable();
            $table->string('status')->nullable();
            $table->string('job_title')->nullable();
            $table->string('photo')->nullable();
            $table->string('cv_path')->nullable();
             $table->foreignId('filiere_id')->constrained('filieres')->onDelete('cascade');
            $table->string('promotion')->nullable();
          

            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alumni_profiles');
    }
};
