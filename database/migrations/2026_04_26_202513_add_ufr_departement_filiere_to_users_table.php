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
    Schema::table('users', function (Blueprint $table) {
        $table->foreignId('ufr_id')->nullable()->constrained()->nullOnDelete();
        $table->foreignId('departement_id')->nullable()->constrained()->nullOnDelete();
        $table->foreignId('filiere_id')->nullable()->constrained()->nullOnDelete();
    });
}

public function down(): void
{
    Schema::table('users', function (Blueprint $table) {
        $table->dropForeign(['ufr_id']);
        $table->dropForeign(['departement_id']);
        $table->dropForeign(['filiere_id']);

        $table->dropColumn(['ufr_id','departement_id','filiere_id']);
    });
}
};
