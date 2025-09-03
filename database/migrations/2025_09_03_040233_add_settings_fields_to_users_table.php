<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('bio', 500)->nullable();
            $table->string('theme', 20)->default('dark');
            $table->string('language', 10)->default('en');
            $table->string('date_format', 20)->default('MM/DD/YYYY');
            $table->string('time_format', 10)->default('12h');
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['bio', 'theme', 'language', 'date_format', 'time_format']);
        });
    }
};
