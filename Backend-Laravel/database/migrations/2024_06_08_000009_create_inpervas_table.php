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
        Schema::create('inpervas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_ip_publik');
            $table->string('name');
            $table->timestamps();

            $table->foreign('id_ip_publik')->references('id')->on('ip_publiks')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inpervas');
    }
}; 