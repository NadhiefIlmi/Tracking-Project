<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dependency_packages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_deployment');
            $table->string('dependency_name');
            $table->string('version');
            $table->timestamps();

            $table->foreign('id_deployment')->references('id')->on('deployments')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dependency_packages');
    }
}; 