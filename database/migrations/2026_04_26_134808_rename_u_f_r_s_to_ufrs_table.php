<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::rename('u_f_r_s', 'ufrs');
    }

    public function down(): void
    {
        Schema::rename('ufrs', 'u_f_r_s');
    }
};