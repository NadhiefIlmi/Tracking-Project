<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_api_gates', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->unsignedBigInteger('id_deployment');
            $table->string('api_gate');
            $table->string('service_name');
            $table->string('protocol');
            $table->string('path');
            $table->timestamps();

            $table->foreign('id_deployment')->references('id')->on('deployments')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_api_gates');
    }
}; 