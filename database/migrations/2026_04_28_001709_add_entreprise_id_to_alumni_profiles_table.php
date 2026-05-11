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
    Schema::table('alumni_profiles', function (Blueprint $table) {
        $table->foreignId('entreprise_id')
            ->nullable()
            ->constrained()
            ->onDelete('set null');
    });
}

public function down()
{
    Schema::table('alumni_profiles', function (Blueprint $table) {
        $table->dropForeign(['entreprise_id']);
        $table->dropColumn('entreprise_id');
    });
}
};
