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
        $table->timestamp('last_seen')->nullable();
    });
}

    /**
     * Reverse the migrations.
     */
   public function down(): void
{
    Schema::table('messages', function (Blueprint $table) {
        if (Schema::hasColumn('messages', 'deleted_for_everyone')) {
            $table->dropColumn('deleted_for_everyone');
        }
    });
}
};
