<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('deployments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_project');
            $table->string('namespace');
            $table->string('service_name');
            $table->string('type');
            $table->string('cluster_ip');
            $table->string('external_ip');
            $table->string('port');
            $table->string('jenis_app');
            $table->string('tahap');
            $table->string('env');
            $table->timestamps();

            $table->foreign('id_project')->references('id')->on('projects')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deployments');
    }
}; 