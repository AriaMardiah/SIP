<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('report_services', function (Blueprint $table) {
            // hapus kolom lama
            $table->dropColumn('media_pelaporan');

            // tambah kolom baru
            $table->unsignedBigInteger('id_media')->after('service_id');

            // foreign key
            $table->foreign('id_media')
                ->references('id')
                ->on('media_pelaporan')
                ->onDelete('cascade');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('report_services', function (Blueprint $table) {
            // hapus FK & kolom
            $table->dropForeign(['id_media']);
            $table->dropColumn('id_media');

            // kembalikan kolom lama
            $table->string('media_pelaporan')->nullable();
        });
    }
};
