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
        Schema::create('wafs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_deployment');
            $table->string('ip_waf');
            $table->timestamps();

            $table->foreign('id_deployment')->references('id')->on('deployments')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wafs');
    }
}; 