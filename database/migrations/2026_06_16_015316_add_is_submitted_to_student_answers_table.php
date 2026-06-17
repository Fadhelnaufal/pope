<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('student_answers', function (Blueprint $table) {
            // Menambah penanda apakah lembar kerja sudah dikumpulkan permanen
            $table->boolean('is_submitted')->default(false)->after('score');
        });
    }

    public function down()
    {
        Schema::table('student_answers', function (Blueprint $table) {
            $table->dropColumn('is_submitted');
        });
    }
};