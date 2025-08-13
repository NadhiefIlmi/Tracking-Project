<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('apps', function (Blueprint $table) {
            $table->id();
            $table->string('proses_bisnis');
            $table->string('nama_singkat_proses_bisnis');
            $table->string('unit_kerja_pemilik_proses_bisnis');
            $table->string('jenis_layanan');
            $table->string('nama_aplikasi');
            $table->uuid('penanggung_jawab_aplikasi');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('apps');
    }
}; 