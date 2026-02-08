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
        Schema::create('report_services', function (Blueprint $table) {
            $table->id();
            $table->string('nama_konsumen');
            $table->string('instansi')->nullable();
            $table->string('email_konsumen')->nullable();
            $table->string('no_hp_konsumen')->nullable();
            $table->foreignId('service_id')->constrained('services')->onDelete('cascade');
            $table->string('media_pelaporan');
            $table->text('uraian');
            $table->enum('status', ['progress', 'selesai'])->default('progress');
            $table->text('tindak_lanjut')->nullable();
            $table->string('dokumentasi')->nullable();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('penerima')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('report_services');
    }
};
