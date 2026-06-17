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
    Schema::table('student_answers', function (Blueprint $table) {
        // Tambahkan kolom file_path (nullable karena siswa mungkin hanya kirim teks)
        $table->string('file_path')->nullable()->after('answer_data');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_answers', function (Blueprint $table) {
            //
        });
    }
};
