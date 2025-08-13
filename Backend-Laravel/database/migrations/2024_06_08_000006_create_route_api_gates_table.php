<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('route_api_gates', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->unsignedBigInteger('id_app');
            $table->uuid('id_service_api_gate');
            $table->string('route_name');
            $table->string('path');
            $table->string('protocol');
            $table->timestamps();

            $table->foreign('id_app')->references('id')->on('apps')->onDelete('cascade');
            $table->foreign('id_service_api_gate')->references('id')->on('service_api_gates')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('route_api_gates');
    }
}; 